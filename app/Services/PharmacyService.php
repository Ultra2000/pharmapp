<?php

namespace App\Services;

use App\Models\PharmacyInfo;

class PharmacyService
{
    public function getPharmacyInfo(): ?PharmacyInfo
    {
        return PharmacyInfo::first();
    }
}
