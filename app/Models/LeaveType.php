<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveType extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'code',
        'max_days_per_year',
        'is_paid',
        'is_carry_forward',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'max_days_per_year' => 'integer',
            'is_paid' => 'boolean',
            'is_carry_forward' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
