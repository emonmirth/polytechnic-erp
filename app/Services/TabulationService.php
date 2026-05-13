<?php

namespace App\Services;

use App\Models\Result;
use App\Models\Subject;
use Illuminate\Support\Collection;

class TabulationService
{
    /**
     * @return array{subject_headers:Collection<int,string>,rows:Collection<int,array<string,mixed>>}
     */
    public function getSemesterReport(int $departmentId, int $semesterId, int $sessionId): array
    {
        $results = Result::query()
            ->with(['student', 'items'])
            ->whereHas('student', fn ($query) => $query->where('department_id', $departmentId))
            ->where('semester_id', $semesterId)
            ->where('session_id', $sessionId)
            ->orderBy('student_id')
            ->get();

        $subjects = Subject::query()
            ->where('department_id', $departmentId)
            ->where('semester_id', $semesterId)
            ->orderBy('subject_code')
            ->get(['subject_code']);

        $subjectHeaders = $subjects->pluck('subject_code');

        $rows = $results->map(function (Result $result) use ($subjectHeaders): array {
            $subjectMap = $result->items
                ->keyBy('subject_code_snapshot');

            $subjectCells = [];
            foreach ($subjectHeaders as $code) {
                $item = $subjectMap->get($code);
                $subjectCells[$code] = [
                    'total' => $item?->total_marks ?? '-',
                    'grade' => $item?->letter_grade ?? '-', 
                    'gp' => $item?->grade_point ?? '-',
                ];
            }

            return [
                'roll_no' => $result->student?->roll_no,
                'student_name' => $result->student?->name,
                'subjects' => $subjectCells,
                'gpa' => (float) $result->gpa,
                'status' => $result->status,
            ];
        });

        return [
            'subject_headers' => $subjectHeaders,
            'rows' => $rows,
        ];
    }
}
