<?php

namespace App\Services\Academic;

use App\Models\Mark;
use App\Models\Result;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SemesterResultGenerationService
{
    public function __construct(
        private readonly BtebGradingService $gradingService,
    ) {
    }

    public function generate(int $sessionId, int $semesterId, string $status = 'published'): void
    {
        $students = Student::where('session_id', $sessionId)->get();

        foreach ($students as $student) {
            DB::transaction(function () use ($student, $semesterId, $sessionId, $status): void {
                $subjects = Subject::where('department_id', $student->department_id)
                    ->where('semester_id', $semesterId)
                    ->get();

                if ($subjects->isEmpty()) {
                    return;
                }

                $totalGradePoints = 0;
                $totalCredits = 0.0;
                $totalMarks = 0;
                $hasReferred = false;
                $referredSubjectCodes = [];

                $resultItemsData = [];

                foreach ($subjects as $subject) {
                    $mark = Mark::where('student_id', $student->id)
                        ->where('subject_id', $subject->id)
                        ->where('semester_id', $semesterId)
                        ->first();

                    $marks = [
                        'tc_mark' => $mark?->tc_mark ?? 0,
                        'tf_mark' => $mark?->tf_mark ?? 0,
                        'pc_mark' => $mark?->pc_mark ?? 0,
                        'pf_mark' => $mark?->pf_mark ?? 0,
                    ];

                    $evaluated = $this->gradingService->evaluate(
                        $subject,
                        $marks,
                        (bool) ($mark?->is_absent ?? true),
                    );

                    if ($evaluated['is_referred']) {
                        $hasReferred = true;
                        $referredSubjectCodes[] = $subject->subject_code;
                    }

                    $credit = (float) $subject->credit;
                    $totalGradePoints += ((float) $evaluated['grade_point'] * $credit);
                    $totalCredits += $credit;
                    $totalMarks += (int) $evaluated['total_marks'];

                    $resultItemsData[] = [
                        'subject_id' => $subject->id,
                        'subject_name_snapshot' => $subject->name,
                        'subject_code_snapshot' => $subject->subject_code,
                        'credit_snapshot' => $credit,
                        'grade_point' => $evaluated['grade_point'],
                        'letter_grade' => $evaluated['letter_grade'],
                        'tc_mark' => $marks['tc_mark'],
                        'tf_mark' => $marks['tf_mark'],
                        'pc_mark' => $marks['pc_mark'],
                        'pf_mark' => $marks['pf_mark'],
                        'total_marks' => $evaluated['total_marks'],
                        'status' => $evaluated['status'],
                    ];
                }

                $gpa = $totalCredits > 0 ? round($totalGradePoints / $totalCredits, 2) : 0.00;
                $publicationStatus = $status === 'published' ? 'published' : 'draft';

                $result = Result::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'semester_id' => $semesterId,
                        'session_id' => $sessionId,
                    ],
                    [
                        'gpa' => $gpa,
                        'cgpa' => $gpa,
                        'total_marks' => $totalMarks,
                        'status' => $hasReferred ? 'REFERRED' : 'PASSED',
                        'publication_status' => $publicationStatus,
                        'published_at' => $publicationStatus === 'published' ? now() : null,
                        'verification_token' => $publicationStatus === 'published' ? (string) Str::uuid() : null,
                        'referred_subject_codes' => $referredSubjectCodes,
                        'snapshot_hash' => null,
                        'transcript_generated_at' => null,
                    ]
                );

                $result->items()->delete();
                foreach ($resultItemsData as $itemData) {
                    $result->items()->create($itemData);
                }
            });
        }
    }
}
