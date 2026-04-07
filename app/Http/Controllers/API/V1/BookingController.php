<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\API\V1\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\API\V1\BookingResource;
use App\Http\Resources\API\V1\BookingResultResource;
use App\Models\Admin;
use App\Models\Booking;
use App\Models\Owner;
use App\Models\Partner;
use App\Models\User;
use App\Services\BookingService;
use App\Services\MediaUploadService;
use App\Services\PartnerAssignmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BookingController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly BookingService $bookingService,
        private readonly PartnerAssignmentService $partnerAssignmentService,
        private readonly MediaUploadService $mediaUploadService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $actor = $this->requireActor($request, [User::class, Partner::class, Owner::class, Admin::class]);

        $query = Booking::query()
            ->with(['user', 'category', 'plan', 'assignedPartner', 'payments', 'results'])
            ->filter($request->only(['booking_id', 'status', 'date', 'category_id', 'plan_id', 'partner_id', 'payment_status', 'user']))
            ->latest();

        if ($actor instanceof User) {
            $query->where('user_id', $actor->id);
        } elseif ($actor instanceof Partner) {
            $query->where('assigned_partner_id', $actor->id);
        } else {
            $query
                ->when($request->filled('user_id'), fn ($builder) => $builder->where('user_id', $request->integer('user_id')))
                ->when($request->filled('assigned_partner_id'), fn ($builder) => $builder->where('assigned_partner_id', $request->integer('assigned_partner_id')));
        }

        return $this->success(BookingResource::collection($query->paginate(15))->response()->getData(true), 'Bookings fetched');
    }

    public function store(Request $request): JsonResponse
    {
        $actor = $this->requireActor($request, [User::class]);

        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'plan_id' => ['required', 'exists:plans,id'],
            'booking_date' => ['required', 'date'],
            'booking_time' => ['required', 'date_format:H:i'],
            'address' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $booking = $this->bookingService->create([
            ...$data,
            'user_id' => $actor->id,
        ]);

        return $this->success(new BookingResource($booking->load(['user', 'plan', 'category'])), 'Booking created', 201);
    }

    public function show(Request $request, Booking $booking): JsonResponse
    {
        $actor = $this->requireActor($request, [User::class, Partner::class, Owner::class, Admin::class]);

        $this->authorizeBookingAccess($booking, $actor);

        $booking->load(['user', 'category', 'plan', 'assignedPartner', 'statusLogs', 'payments', 'results']);

        return $this->success(new BookingResource($booking), 'Booking fetched');
    }

    public function assignPartner(Request $request, Booking $booking): JsonResponse
    {
        $actor = $this->requireActor($request, [Owner::class, Admin::class]);

        $data = $request->validate([
            'partner_id' => ['required', 'exists:partners,id'],
            'remarks' => ['nullable', 'string'],
        ]);

        $booking = $this->partnerAssignmentService->assign($booking, $data['partner_id'], $actor, $data['remarks'] ?? null);

        return $this->success(new BookingResource($booking->load(['assignedPartner', 'user', 'category', 'plan'])), 'Partner assigned');
    }

    public function updateStatus(Request $request, Booking $booking): JsonResponse
    {
        $actor = $this->requireActor($request, [Partner::class, Owner::class, Admin::class]);

        if ($actor instanceof Partner && (int) $booking->assigned_partner_id !== (int) $actor->id) {
            abort(403, 'You can only update bookings assigned to you.');
        }

        $data = $request->validate([
            'status' => ['required', 'string'],
            'remarks' => ['nullable', 'string'],
        ]);

        $booking = $this->bookingService->updateStatus($booking, $data['status'], $actor, $data['remarks'] ?? null);

        return $this->success(new BookingResource($booking->load(['assignedPartner', 'user', 'category', 'plan'])), 'Booking status updated');
    }

    public function uploadResults(Request $request, Booking $booking): JsonResponse
    {
        $actor = $this->requireActor($request, [Partner::class]);

        if ((int) $booking->assigned_partner_id !== (int) $actor->id) {
            abort(403, 'You can only upload results for bookings assigned to you.');
        }

        $data = $request->validate([
            'results' => ['nullable', 'array', 'min:1'],
            'results.*.file_url' => ['required_with:results', 'url'],
            'results.*.file_type' => ['required_with:results', 'in:photo,video'],
            'results.*.notes' => ['nullable', 'string'],
            'file' => ['nullable', 'file', 'max:51200', 'mimetypes:image/jpeg,image/png,image/webp,video/mp4,video/quicktime,video/webm'],
            'file_type' => ['required_without:results', 'nullable', 'in:photo,video'],
            'notes' => ['nullable', 'string'],
        ]);

        $resultsPayload = $data['results'] ?? null;

        if ($request->hasFile('file')) {
            $path = $this->mediaUploadService->upload(
                $request->file('file'),
                "booking-results/{$booking->id}"
            );

            $resultsPayload = [[
                'file_url' => Storage::disk('public')->url((string) $path),
                'file_type' => $data['file_type'],
                'notes' => $data['notes'] ?? null,
            ]];
        }

        if (empty($resultsPayload)) {
            abort(422, 'Upload at least one result.');
        }

        $results = $this->bookingService->uploadResults($booking, $actor->id, $resultsPayload);

        return $this->success(BookingResultResource::collection(collect($results)), 'Booking results uploaded');
    }

    private function requireActor(Request $request, array $allowedClasses): User|Partner|Owner|Admin
    {
        $actor = $request->user('sanctum');

        foreach ($allowedClasses as $allowedClass) {
            if ($actor instanceof $allowedClass) {
                return $actor;
            }
        }

        abort(403, 'You are not authorized to perform this action.');
    }

    private function authorizeBookingAccess(Booking $booking, User|Partner|Owner|Admin $actor): void
    {
        if ($actor instanceof Owner || $actor instanceof Admin) {
            return;
        }

        if ($actor instanceof User && (int) $booking->user_id === (int) $actor->id) {
            return;
        }

        if ($actor instanceof Partner && (int) $booking->assigned_partner_id === (int) $actor->id) {
            return;
        }

        abort(403, 'You are not allowed to access this booking.');
    }
}
