<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'iso_code',
        'iso3_code',
        'phone_code',
        'default_currency_id',
        'tax_type',
        'tax_percentage',
        'tax_registration_format',
        'invoice_number_format',
        'date_format',
        'financial_year_format',
        'address_style',
        'accounting_rules_profile',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'tax_percentage' => 'decimal:4',
            'is_active' => 'boolean',
        ];
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'default_currency_id');
    }

    public function taxProfiles(): HasMany
    {
        return $this->hasMany(TaxProfile::class);
    }
}
