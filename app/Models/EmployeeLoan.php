<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeLoan extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'loan_type',
        'amount',
        'interest_rate',
        'installment_amount',
        'total_installments',
        'paid_installments',
        'outstanding_amount',
        'start_date',
        'end_date',
        'status',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'interest_rate' => 'decimal:4',
            'installment_amount' => 'decimal:2',
            'total_installments' => 'integer',
            'paid_installments' => 'integer',
            'outstanding_amount' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
