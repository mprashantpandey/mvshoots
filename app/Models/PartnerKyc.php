<?php

namespace App\Models;

use App\Enums\PartnerKycStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PartnerKyc extends Model
{
    protected $table = 'partner_kyc';

    protected $fillable = [
        'partner_id',
        'aadhar_number',
        'aadhar_front_path',
        'aadhar_back_path',
        'pan_number',
        'pan_image_path',
        'selfie_path',
        'status',
        'rejection_reason',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'status' => PartnerKycStatus::class,
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::deleting(function (PartnerKyc $kyc): void {
            $disk = Storage::disk('local');
            foreach ([
                $kyc->aadhar_front_path,
                $kyc->aadhar_back_path,
                $kyc->pan_image_path,
                $kyc->selfie_path,
            ] as $path) {
                if ($path && $disk->exists($path)) {
                    $disk->delete($path);
                }
            }
        });
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function reviewedByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'reviewed_by');
    }
}
