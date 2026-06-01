<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    protected $fillable = [
        'company_name', 'logo', 'tagline',
        'address_line1', 'address_line2', 'city', 'state', 'pincode', 'country',
        'phone', 'email', 'website',
        'gstin', 'pan', 'cin', 'epf_registration_no', 'esic_registration_no', 'pt_registration_no',
    ];

    // always returns the single settings record, creates if not exists
    public static function get(): self
    {
        return static::firstOrCreate(['id' => 1], [
            'company_name' => 'My Company',
            'country'      => 'India',
        ]);
    }
}
