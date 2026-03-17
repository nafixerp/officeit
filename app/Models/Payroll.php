<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'employee_id',
        'payroll_month',
        'payroll_year',
        'basic_salary',
        'total_earnings',
        'total_deductions',
        'net_salary',
        'currency_id',
        'payment_date',
        'payment_method',
        'bank_account_id',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'payroll_month' => 'integer',
            'payroll_year' => 'integer',
            'basic_salary' => 'decimal:2',
            'total_earnings' => 'decimal:2',
            'total_deductions' => 'decimal:2',
            'net_salary' => 'decimal:2',
            'payment_date' => 'date',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PayrollItem::class);
    }
}
