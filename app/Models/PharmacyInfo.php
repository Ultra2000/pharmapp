<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PharmacyInfo extends Model
{
    protected $fillable = [
        'name',
        'address',
        'zip',
        'city',
        'phone',
        'email',
        'siret',
        'vat_number',
        'license_number',
        'logo',
        'invoice_footer',
    ];

    protected $casts = [
        'logo' => 'array',
    ];
}
