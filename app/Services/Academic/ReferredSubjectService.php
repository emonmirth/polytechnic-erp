<?php

namespace App\Services\Academic;

use App\Models\Result;
use App\Models\Student;
use Illuminate\Support\Collection;

class ReferredSubjectService
{
    /**
     * @return Collection<int,array<string,mixed>>
     */
    public function getStudentReferredSubjects(int $studentId): Collection
    {
        return Result::query()
            ->with(['semester', 'items'])
            ->where('student_id', $studentId)
            ->orderBy('semester_id')
            ->get()
            ->flatMap(function (Result $result): Collection {
                return $result->items
                    ->where('status', 'REFERRED')
                    ->map(fn ($item) => [
                        'semester' => $result->semester?->name,
                        'subject_code' => $item->subject_code_snapshot,
                        'subject_name' => $item->subject_name_snapshot,
                        'grade' => $item->letter_grade,
                    ]);
            });
    }

    public function countStudentReferredSubjects(int $studentId): int
    {
        return $this->getStudentReferredSubjects($studentId)->count();
    }
}
