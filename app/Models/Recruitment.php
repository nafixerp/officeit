<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Recruitment extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'title',
        'department_id',
        'designation_id',
        'vacancies',
        'description',
        'requirements',
        'location',
        'employment_type',
        'salary_range_min',
        'salary_range_max',
        'opening_date',
        'closing_date',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'vacancies' => 'integer',
            'salary_range_min' => 'decimal:2',
            'salary_range_max' => 'decimal:2',
            'opening_date' => 'date',
            'closing_date' => 'date',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(Candidate::class);
    }
}
