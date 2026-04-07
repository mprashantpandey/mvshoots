<?php

namespace App\Http\Resources\API\V1;

use App\Enums\PartnerKycStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PartnerKycResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'status' => $this->status?->value,
            'aadhar_number_masked' => $this->maskedAadhar($this->aadhar_number ?? ''),
            'pan_number_masked' => $this->maskedPan($this->pan_number ?? ''),
            'rejection_reason' => $this->when(
                $this->status === PartnerKycStatus::Rejected,
                $this->rejection_reason
            ),
            'submitted_at' => $this->submitted_at?->toISOString(),
            'reviewed_at' => $this->reviewed_at?->toISOString(),
            'can_resubmit' => $this->status === PartnerKycStatus::Rejected,
        ];
    }

    private function maskedAadhar(string $aadhar): ?string
    {
        if (strlen($aadhar) < 4) {
            return null;
        }

        return 'XXXX XXXX '.substr($aadhar, -4);
    }

    private function maskedPan(string $pan): ?string
    {
        if (strlen($pan) < 4) {
            return null;
        }

        return 'XXXXX'.substr($pan, -5);
    }
}
