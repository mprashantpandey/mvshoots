<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PartnerKycStatus;
use App\Http\Controllers\Admin\Concerns\AuthorizesAdminCity;
use App\Http\Requests\Admin\PartnerRequest;
use App\Models\City;
use App\Models\Partner;
use App\Support\AdminCityScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PartnerController
{
    use AuthorizesAdminCity;

    /**
     * Partners with KYC status pending (awaiting admin review).
     */
    public function pendingKyc(Request $request): Response
    {
        $admin = Auth::guard('admin')->user();
        $filters = $request->only(['search']);

        $query = Partner::query();
        AdminCityScope::partners($query, $admin);
        $query
            ->join('partner_kyc', 'partners.id', '=', 'partner_kyc.partner_id')
            ->where('partner_kyc.status', PartnerKycStatus::Pending->value)
            ->select('partners.*')
            ->when(
                $request->filled('search'),
                function ($q) use ($request): void {
                    $term = '%'.$request->string('search')->toString().'%';
                    $q->where(function ($inner) use ($term): void {
                        $inner->where('partners.name', 'like', $term)
                            ->orWhere('partners.phone', 'like', $term)
                            ->orWhere('partners.email', 'like', $term);
                    });
                }
            )
            ->with(['managedCity', 'serviceCities', 'kyc'])
            ->withAvg('ratings', 'rating')
            ->withCount(['assignedBookings', 'ratings'])
            ->orderByDesc('partner_kyc.submitted_at');

        return Inertia::render('Admin/Partners/KycPending', [
            'partners' => $query
                ->paginate(20)
                ->withQueryString()
                ->through(fn (Partner $partner) => $this->transformPartner($partner)),
            'filters' => $filters,
        ]);
    }

    public function index(Request $request): Response
    {
        $admin = Auth::guard('admin')->user();
        $filters = $request->only(['search', 'status', 'city_id', 'kyc_status']);
        if ($admin->city_id) {
            $filters['city_id'] = (string) $admin->city_id;
        }

        $partnerQuery = Partner::query();
        AdminCityScope::partners($partnerQuery, $admin);

        return Inertia::render('Admin/Partners/Index', [
            'partners' => $partnerQuery
                ->with(['managedCity', 'serviceCities', 'kyc'])
                ->withAvg('ratings', 'rating')
                ->withCount(['assignedBookings', 'ratings'])
                ->filter($filters)
                ->latest()
                ->paginate(20)
                ->withQueryString()
                ->through(fn (Partner $partner) => $this->transformPartner($partner)),
            'filters' => $filters,
            'cities' => $this->cityOptions(),
        ]);
    }

    public function create(): Response
    {
        $admin = Auth::guard('admin')->user();

        return Inertia::render('Admin/Partners/Form', [
            'partner' => null,
            'cities' => $this->cityOptions(),
            'city_admin_locked' => $admin && $admin->city_id,
            'submitUrl' => route('admin.partners.store'),
            'method' => 'post',
        ]);
    }

    public function store(PartnerRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $serviceCityIds = $data['service_city_ids'] ?? [];
        unset($data['service_city_ids']);

        $partner = Partner::create($data);
        $partner->serviceCities()->sync($serviceCityIds);

        return redirect()->route('admin.partners.index')->with('status', 'Partner created.');
    }

    public function show(Partner $partner): Response
    {
        $this->abortUnlessPartnerInScope($partner);

        $partner->load(['managedCity', 'serviceCities', 'assignedBookings.user', 'assignedBookings.plan', 'bookingResults', 'kyc.reviewedByAdmin']);
        $partner->load(['ratings' => fn ($q) => $q->with(['booking.plan', 'user'])->latest()->limit(50)]);
        $partner->loadAvg('ratings', 'rating');
        $partner->loadCount('ratings');

        return Inertia::render('Admin/Partners/Show', [
            'partner' => $this->transformPartner($partner->loadCount('assignedBookings'), true),
        ]);
    }

    public function edit(Partner $partner): Response
    {
        $this->abortUnlessPartnerInScope($partner);

        $admin = Auth::guard('admin')->user();

        return Inertia::render('Admin/Partners/Form', [
            'partner' => $this->transformPartner($partner->load(['managedCity', 'serviceCities'])->loadCount('assignedBookings'), true),
            'cities' => $this->cityOptions(),
            'city_admin_locked' => $admin && $admin->city_id,
            'submitUrl' => route('admin.partners.update', $partner),
            'method' => 'put',
        ]);
    }

    public function update(PartnerRequest $request, Partner $partner): RedirectResponse
    {
        $this->abortUnlessPartnerInScope($partner);

        $data = $request->validated();
        $serviceCityIds = $data['service_city_ids'] ?? [];
        unset($data['service_city_ids']);

        $partner->update($data);
        $partner->serviceCities()->sync($serviceCityIds);

        return redirect()->route('admin.partners.index')->with('status', 'Partner updated.');
    }

    public function updateStatus(Request $request, Partner $partner): RedirectResponse
    {
        $this->abortUnlessPartnerInScope($partner);

        $data = $request->validate([
            'status' => ['required', 'in:active,inactive'],
        ]);

        $partner->update([
            'status' => $data['status'],
        ]);

        return redirect()->back()->with('status', 'Partner status updated.');
    }

    public function destroy(Partner $partner): RedirectResponse
    {
        $this->abortUnlessPartnerInScope($partner);

        $partner->serviceCities()->detach();
        $partner->delete();

        return redirect()->route('admin.partners.index')->with('status', 'Partner deleted.');
    }

    public function verifyKyc(Partner $partner): RedirectResponse
    {
        $this->abortUnlessPartnerInScope($partner);

        $partner->load('kyc');

        if (! $partner->kyc || $partner->kyc->status !== PartnerKycStatus::Pending) {
            return redirect()->back()->withErrors(['kyc' => 'There is no pending KYC to verify for this partner.']);
        }

        $partner->kyc->update([
            'status' => PartnerKycStatus::Verified,
            'reviewed_at' => now(),
            'reviewed_by' => Auth::guard('admin')->id(),
            'rejection_reason' => null,
        ]);

        return redirect()->back()->with('status', 'KYC verified. This partner can now be assigned bookings.');
    }

    public function rejectKyc(Request $request, Partner $partner): RedirectResponse
    {
        $this->abortUnlessPartnerInScope($partner);

        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:2000'],
        ]);

        $partner->load('kyc');

        if (! $partner->kyc || $partner->kyc->status !== PartnerKycStatus::Pending) {
            return redirect()->back()->withErrors(['kyc' => 'There is no pending KYC to reject for this partner.']);
        }

        $partner->kyc->update([
            'status' => PartnerKycStatus::Rejected,
            'rejection_reason' => $data['rejection_reason'],
            'reviewed_at' => now(),
            'reviewed_by' => Auth::guard('admin')->id(),
        ]);

        return redirect()->back()->with('status', 'KYC rejected. The partner can resubmit documents.');
    }

    public function kycFile(Partner $partner, string $field): StreamedResponse
    {
        $this->abortUnlessPartnerInScope($partner);

        $allowed = ['aadhar_front', 'aadhar_back', 'pan_image', 'selfie'];
        abort_unless(in_array($field, $allowed, true), 404);

        $partner->load('kyc');
        abort_unless($partner->kyc, 404);

        $pathColumn = match ($field) {
            'aadhar_front' => 'aadhar_front_path',
            'aadhar_back' => 'aadhar_back_path',
            'pan_image' => 'pan_image_path',
            'selfie' => 'selfie_path',
        };

        $path = $partner->kyc->{$pathColumn};
        $disk = Storage::disk('local');

        abort_unless($path && $disk->exists($path), 404);

        return $disk->response($path);
    }

    private function transformPartner(Partner $partner, bool $detailed = false): array
    {
        $payload = [
            'id' => $partner->id,
            'name' => $partner->name,
            'phone' => $partner->phone,
            'email' => $partner->email,
            'city_id' => $partner->city_id,
            'city_name' => $partner->managedCity?->name,
            'service_city_ids' => $partner->relationLoaded('serviceCities') ? $partner->serviceCities->pluck('id')->values()->all() : [],
            'service_cities' => $partner->relationLoaded('serviceCities') ? $partner->serviceCities->pluck('name')->values()->all() : [],
            'status' => $partner->status,
            'assigned_bookings_count' => $partner->assigned_bookings_count ?? $partner->assignedBookings()->count(),
            'show_url' => route('admin.partners.show', $partner),
            'edit_url' => route('admin.partners.edit', $partner),
            'delete_url' => route('admin.partners.destroy', $partner),
            'status_url' => route('admin.partners.update-status', $partner),
            'kyc_status' => $partner->kyc?->status?->value ?? 'not_submitted',
            'verify_kyc_url' => ($partner->kyc?->status === PartnerKycStatus::Pending)
                ? route('admin.partners.kyc.verify', $partner)
                : null,
            'reject_kyc_url' => ($partner->kyc?->status === PartnerKycStatus::Pending)
                ? route('admin.partners.kyc.reject', $partner)
                : null,
            'kyc_submitted_at' => $partner->kyc?->submitted_at?->format('d M Y, H:i'),
            'kyc_submitted_at_iso' => $partner->kyc?->submitted_at?->toIso8601String(),
            'rating_average' => $partner->ratings_avg_rating !== null
                ? round((float) $partner->ratings_avg_rating, 2)
                : null,
            'ratings_count' => (int) ($partner->ratings_count ?? 0),
        ];

        if ($detailed) {
            $payload['assigned_bookings'] = $partner->assignedBookings->map(fn ($booking) => [
                'id' => $booking->id,
                'user_name' => $booking->user?->name,
                'plan_name' => $booking->plan?->title,
                'status' => $booking->status,
                'show_url' => route('admin.bookings.show', $booking),
            ])->values();
            $payload['booking_results'] = $partner->bookingResults->map(fn ($result) => [
                'id' => $result->id,
                'file_type' => $result->file_type,
                'notes' => $result->notes,
                'file_url' => $result->file_url,
            ])->values();
            if ($partner->relationLoaded('kyc') && $partner->kyc) {
                $kyc = $partner->kyc;
                $payload['kyc_detail'] = [
                    'status' => $kyc->status->value,
                    'aadhar_number' => $kyc->aadhar_number,
                    'pan_number' => $kyc->pan_number,
                    'rejection_reason' => $kyc->rejection_reason,
                    'submitted_at' => $kyc->submitted_at?->toIso8601String(),
                    'reviewed_at' => $kyc->reviewed_at?->toIso8601String(),
                    'reviewed_by_name' => $kyc->reviewedByAdmin?->name,
                    'aadhar_front_url' => route('admin.partners.kyc.file', [$partner, 'aadhar_front']),
                    'aadhar_back_url' => route('admin.partners.kyc.file', [$partner, 'aadhar_back']),
                    'pan_image_url' => route('admin.partners.kyc.file', [$partner, 'pan_image']),
                    'selfie_url' => route('admin.partners.kyc.file', [$partner, 'selfie']),
                ];
            } else {
                $payload['kyc_detail'] = null;
            }

            if ($partner->relationLoaded('ratings')) {
                $payload['recent_ratings'] = $partner->ratings->map(fn ($r) => [
                    'id' => $r->id,
                    'booking_id' => $r->booking_id,
                    'rating' => $r->rating,
                    'review' => $r->review,
                    'customer_name' => $r->user?->name,
                    'plan_title' => $r->booking?->plan?->title,
                    'booking_date' => $r->booking?->booking_date?->format('d M Y'),
                    'created_at' => $r->created_at?->toIso8601String(),
                ])->values();
            }
        }

        return $payload;
    }

    private function cityOptions(): array
    {
        $admin = Auth::guard('admin')->user();

        if ($admin && $admin->city_id) {
            $city = City::query()
                ->where('id', $admin->city_id)
                ->where('status', 'active')
                ->first(['id', 'name']);

            return $city ? [['id' => $city->id, 'name' => $city->name]] : [];
        }

        return City::query()
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (City $city) => [
                'id' => $city->id,
                'name' => $city->name,
            ])
            ->all();
    }
}
