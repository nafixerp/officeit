<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Candidate extends Model
{
    use HasFactory;

    protected $fillable = [
        'recruitment_id',
        'name',
        'email',
        'phone',
        'resume_path',
        'cover_letter',
        'experience_years',
        'current_salary',
        'expected_salary',
        'status',
        'interview_date',
        'interview_notes',
        'rating',
    ];

    protected function casts(): array
    {
        return [
            'experience_years' => 'integer',
            'current_salary' => 'decimal:2',
            'expected_salary' => 'decimal:2',
            'interview_date' => 'datetime',
            'rating' => 'integer',
        ];
    }

    public function recruitment(): BelongsTo
    {
        return $this->belongsTo(Recruitment::class);
    }
}
