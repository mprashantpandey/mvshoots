<?php

namespace App\Http\Controllers\API\V1;

use App\Enums\PartnerKycStatus;
use App\Http\Controllers\API\V1\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\API\V1\PartnerKycResource;
use App\Models\Partner;
use App\Models\PartnerKyc;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PartnerKycController extends Controller
{
    use ApiResponse;

    public function show(Request $request): JsonResponse
    {
        $partner = $this->requirePartner($request);

        $partner->loadMissing('kyc');

        if (! $partner->kyc) {
            return $this->success([
                'status' => 'not_submitted',
                'aadhar_number_masked' => null,
                'pan_number_masked' => null,
                'rejection_reason' => null,
                'submitted_at' => null,
                'reviewed_at' => null,
                'can_resubmit' => true,
            ], 'KYC status');
        }

        return $this->success(new PartnerKycResource($partner->kyc), 'KYC status');
    }

    public function store(Request $request): JsonResponse
    {
        $partner = $this->requirePartner($request);

        $data = $request->validate([
            'aadhar_number' => ['required', 'digits:12'],
            'pan_number' => ['required', 'string', 'size:10', 'regex:/^[A-Za-z]{5}[0-9]{4}[A-Za-z]$/'],
            'aadhar_front' => ['required', 'file', 'max:5120', 'mimetypes:image/jpeg,image/png,image/webp'],
            'aadhar_back' => ['required', 'file', 'max:5120', 'mimetypes:image/jpeg,image/png,image/webp'],
            'pan_image' => ['required', 'file', 'max:5120', 'mimetypes:image/jpeg,image/png,image/webp'],
            'selfie' => ['required', 'file', 'max:5120', 'mimetypes:image/jpeg,image/png,image/webp'],
        ]);

        $data['pan_number'] = strtoupper($data['pan_number']);

        $existing = $partner->kyc;

        if ($existing && $existing->status === PartnerKycStatus::Pending) {
            throw ValidationException::withMessages([
                'kyc' => ['Your KYC is already under review.'],
            ]);
        }

        if ($existing && $existing->status === PartnerKycStatus::Verified) {
            throw ValidationException::withMessages([
                'kyc' => ['Your KYC is already verified. Contact support to update documents.'],
            ]);
        }

        $disk = Storage::disk('local');
        $dir = "kyc/partners/{$partner->id}";

        if ($existing) {
            $existing->delete();
        }

        $aadharFront = $disk->putFile($dir, $request->file('aadhar_front'));
        $aadharBack = $disk->putFile($dir, $request->file('aadhar_back'));
        $panImage = $disk->putFile($dir, $request->file('pan_image'));
        $selfie = $disk->putFile($dir, $request->file('selfie'));

        $kyc = PartnerKyc::create([
            'partner_id' => $partner->id,
            'aadhar_number' => $data['aadhar_number'],
            'aadhar_front_path' => $aadharFront,
            'aadhar_back_path' => $aadharBack,
            'pan_number' => $data['pan_number'],
            'pan_image_path' => $panImage,
            'selfie_path' => $selfie,
            'status' => PartnerKycStatus::Pending,
            'rejection_reason' => null,
            'submitted_at' => now(),
            'reviewed_at' => null,
            'reviewed_by' => null,
        ]);

        return $this->success(new PartnerKycResource($kyc), 'KYC submitted for review', 201);
    }

    public function file(Request $request, string $field): StreamedResponse|JsonResponse
    {
        $partner = $this->requirePartner($request);

        $allowed = ['aadhar_front', 'aadhar_back', 'pan_image', 'selfie'];
        if (! in_array($field, $allowed, true)) {
            abort(404);
        }

        $kyc = $partner->kyc;
        if (! $kyc) {
            abort(404);
        }

        $pathColumn = match ($field) {
            'aadhar_front' => 'aadhar_front_path',
            'aadhar_back' => 'aadhar_back_path',
            'pan_image' => 'pan_image_path',
            'selfie' => 'selfie_path',
        };

        $path = $kyc->{$pathColumn};
        $disk = Storage::disk('local');

        if (! $path || ! $disk->exists($path)) {
            abort(404);
        }

        return $disk->response($path);
    }

    private function requirePartner(Request $request): Partner
    {
        $actor = $request->user('sanctum');
        abort_unless($actor instanceof Partner, 403, 'Only partners can access KYC.');

        return $actor;
    }
}
