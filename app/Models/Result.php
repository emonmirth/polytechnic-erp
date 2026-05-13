<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class Result extends Model
{
    protected $fillable = [
        'student_id', 'semester_id', 'session_id', 'gpa', 'cgpa', 
        'total_marks', 'status', 'publication_status', 'is_locked', 'locked_at',
        'verification_token', 'published_at', 'referred_subject_codes', 'snapshot_hash',
        'transcript_generated_at',
    ];

    protected $casts = [
        'gpa' => 'decimal:2',
        'cgpa' => 'decimal:2',
        'is_locked' => 'boolean',
        'referred_subject_codes' => 'array',
        'locked_at' => 'datetime',
        'published_at' => 'datetime',
        'transcript_generated_at' => 'datetime',
    ];

    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function semester(): BelongsTo { return $this->belongsTo(Semester::class); }
    public function session(): BelongsTo { return $this->belongsTo(Session::class); }
    public function items(): HasMany { return $this->hasMany(ResultItem::class); }

    protected static function booted(): void
    {
        static::saving(function (Result $result): void {
            $maxGpa = 4.00;

            if ((float) $result->gpa < 0 || (float) $result->gpa > $maxGpa) {
                throw ValidationException::withMessages([
                    'gpa' => 'GPA must be between 0.00 and 4.00.',
                ]);
            }

            if ((float) $result->cgpa < 0 || (float) $result->cgpa > $maxGpa) {
                throw ValidationException::withMessages([
                    'cgpa' => 'CGPA must be between 0.00 and 4.00.',
                ]);
            }

            if (
                $result->exists &&
                $result->is_locked &&
                $result->isDirty([
                    'gpa',
                    'cgpa',
                    'total_marks',
                    'status',
                    'publication_status',
                    'referred_subject_codes',
                    'session_id',
                    'semester_id',
                    'student_id',
                ])
            ) {
                throw ValidationException::withMessages([
                    'result' => 'Locked results cannot be altered.',
                ]);
            }
        });
    }
}
