<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditNote extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'note_number',
        'date',
        'customer_id',
        'sales_invoice_id',
        'currency_id',
        'exchange_rate',
        'amount',
        'reason',
        'narration',
        'status',
        'branch_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'exchange_rate' => 'decimal:6',
            'amount' => 'decimal:2',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesInvoice(): BelongsTo
    {
        return $this->belongsTo(SalesInvoice::class);
    }
}
