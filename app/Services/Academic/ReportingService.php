<?php

namespace App\Services\Academic;

use App\Models\Result;
use App\Models\ResultItem;
use App\Models\Semester;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportingService
{
    public function departmentPassPercentageReport(?int $sessionId = null): Collection
    {
        $query = Result::query()
            ->select('students.department_id')
            ->selectRaw('COUNT(results.id) as total_results')
            ->selectRaw("SUM(CASE WHEN results.status = 'PASSED' THEN 1 ELSE 0 END) as passed_results")
            ->join('students', 'students.id', '=', 'results.student_id')
            ->groupBy('students.department_id')
            ->with('student.department');

        if ($sessionId) {
            $query->where('results.session_id', $sessionId);
        }

        $rows = $query->get();

        return $rows->map(function ($row): array {
            $total = (int) $row->total_results;
            $passed = (int) $row->passed_results;

            return [
                'department_id' => (int) $row->department_id,
                'total_results' => $total,
                'passed_results' => $passed,
                'pass_percentage' => $total > 0 ? round(($passed / $total) * 100, 2) : 0.00,
            ];
        });
    }

    public function semesterGpaTrendReport(?int $departmentId = null): Collection
    {
        $query = Result::query()
            ->join('semesters', 'semesters.id', '=', 'results.semester_id')
            ->join('students', 'students.id', '=', 'results.student_id')
            ->select('results.semester_id', 'semesters.name')
            ->selectRaw('AVG(results.gpa) as average_gpa')
            ->groupBy('results.semester_id', 'semesters.name')
            ->orderBy('semesters.level');

        if ($departmentId) {
            $query->where('students.department_id', $departmentId);
        }

        return $query->get()->map(fn ($row) => [
            'semester_id' => (int) $row->semester_id,
            'semester_name' => $row->name,
            'average_gpa' => round((float) $row->average_gpa, 2),
        ]);
    }

    public function topFailingSubjectsReport(int $limit = 10): Collection
    {
        return ResultItem::query()
            ->select('subject_id', 'subject_code_snapshot', 'subject_name_snapshot')
            ->selectRaw("SUM(CASE WHEN status = 'REFERRED' THEN 1 ELSE 0 END) as referred_count")
            ->groupBy('subject_id', 'subject_code_snapshot', 'subject_name_snapshot')
            ->orderByDesc('referred_count')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'subject_id' => (int) $row->subject_id,
                'subject_code' => $row->subject_code_snapshot,
                'subject_name' => $row->subject_name_snapshot,
                'referred_count' => (int) $row->referred_count,
            ]);
    }

    public function resultSummaryStats(): array
    {
        $total = Result::query()->count();
        $published = Result::query()->where('publication_status', 'published')->count();
        $locked = Result::query()->where('is_locked', true)->count();
        $avgGpa = (float) Result::query()->avg('gpa');

        return [
            'total_results' => $total,
            'published_results' => $published,
            'locked_results' => $locked,
            'average_gpa' => round($avgGpa, 2),
        ];
    }
}
