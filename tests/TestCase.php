<?php

namespace Tests;

use App\Enums\PartnerKycStatus;
use App\Models\Partner;
use App\Models\PartnerKyc;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    protected function seedVerifiedPartnerKyc(Partner $partner): PartnerKyc
    {
        return PartnerKyc::create([
            'partner_id' => $partner->id,
            'aadhar_number' => '123456789012',
            'aadhar_front_path' => 'kyc/test/front.jpg',
            'aadhar_back_path' => 'kyc/test/back.jpg',
            'pan_number' => 'ABCDE1234F',
            'pan_image_path' => 'kyc/test/pan.jpg',
            'selfie_path' => 'kyc/test/selfie.jpg',
            'status' => PartnerKycStatus::Verified,
            'submitted_at' => now(),
            'reviewed_at' => now(),
        ]);
    }
}
