<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ApprovalLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'approvable_type',
        'approvable_id',
        'user_id',
        'action',
        'remarks',
        'from_status',
        'to_status',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvable(): MorphTo
    {
        return $this->morphTo();
    }
}
