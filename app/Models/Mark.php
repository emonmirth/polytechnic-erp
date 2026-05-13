<?php

namespace App\Models;

use App\Services\Academic\BtebGradingService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class Mark extends Model
{
    protected $fillable = [
        'student_id', 'subject_id', 'semester_id', 'session_id', 'exam_type',
        'tc_mark', 'tf_mark', 'pc_mark', 'pf_mark', 'total_marks', 
        'grade_point', 'letter_grade', 'exam_year', 'attendance_percentage', 'is_absent', 'is_locked'
    ];

    protected $casts = [
        'is_absent' => 'boolean',
        'is_locked' => 'boolean',
        'attendance_percentage' => 'decimal:2',
        'grade_point' => 'decimal:2',
    ];

    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }
    public function semester(): BelongsTo { return $this->belongsTo(Semester::class); }
    public function session(): BelongsTo { return $this->belongsTo(Session::class, 'session_id'); }

    protected static function booted()
    {
        static::saving(function ($mark) {
            if (
                $mark->exists &&
                $mark->is_locked &&
                $mark->isDirty([
                    'tc_mark',
                    'tf_mark',
                    'pc_mark',
                    'pf_mark',
                    'is_absent',
                    'student_id',
                    'subject_id',
                    'semester_id',
                    'session_id',
                    'exam_year',
                ])
            ) {
                throw ValidationException::withMessages([
                    'mark' => 'Locked marks cannot be modified.',
                ]);
            }

            $subject = $mark->subject()->first();
            if (!$subject) {
                throw ValidationException::withMessages([
                    'subject_id' => 'A valid subject is required for grading.',
                ]);
            }

            $inputBounds = [
                'tc_mark' => [(int) $mark->tc_mark, (int) $subject->tc_marks],
                'tf_mark' => [(int) $mark->tf_mark, (int) $subject->tf_marks],
                'pc_mark' => [(int) $mark->pc_mark, (int) $subject->pc_marks],
                'pf_mark' => [(int) $mark->pf_mark, (int) $subject->pf_marks],
            ];

            foreach ($inputBounds as $key => [$value, $max]) {
                if ($value < 0 || $value > $max) {
                    throw ValidationException::withMessages([
                        $key => "The {$key} must be between 0 and {$max}.",
                    ]);
                }
            }

            $hasLockedResult = \App\Models\Result::query()
                ->where('student_id', $mark->student_id)
                ->where('semester_id', $mark->semester_id)
                ->where('session_id', $mark->session_id)
                ->where('is_locked', true)
                ->exists();

            if ($hasLockedResult) {
                throw ValidationException::withMessages([
                    'result' => 'Cannot update marks because the corresponding result is locked.',
                ]);
            }

            if ($mark->exists && $mark->is_locked) {
                return;
            }

            /** @var BtebGradingService $gradingService */
            $gradingService = app(BtebGradingService::class);
            $grading = $gradingService->evaluate($subject, [
                'tc_mark' => $mark->tc_mark,
                'tf_mark' => $mark->tf_mark,
                'pc_mark' => $mark->pc_mark,
                'pf_mark' => $mark->pf_mark,
            ], (bool) $mark->is_absent);

            $mark->total_marks = $grading['total_marks'];
            $mark->grade_point = $grading['grade_point'];
            $mark->letter_grade = $grading['letter_grade'];
        });
    }
}
