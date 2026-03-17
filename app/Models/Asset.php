<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asset extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'asset_code',
        'name',
        'category',
        'description',
        'purchase_date',
        'purchase_price',
        'current_value',
        'depreciation_method',
        'depreciation_rate',
        'useful_life_years',
        'salvage_value',
        'serial_number',
        'location',
        'assigned_to',
        'branch_id',
        'warranty_expiry',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'purchase_price' => 'decimal:2',
            'current_value' => 'decimal:2',
            'depreciation_rate' => 'decimal:4',
            'useful_life_years' => 'integer',
            'salvage_value' => 'decimal:2',
            'warranty_expiry' => 'date',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'assigned_to');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
