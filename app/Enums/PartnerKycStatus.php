<?php

namespace App\Enums;

enum PartnerKycStatus: string
{
    case Pending = 'pending';
    case Verified = 'verified';
    case Rejected = 'rejected';
}
