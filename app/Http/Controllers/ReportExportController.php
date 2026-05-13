<?php

namespace App\Http\Controllers;

use App\Exports\GenericReportExport;
use App\Http\Requests\ExportReportRequest;
use App\Services\Academic\ReportingService;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportExportController extends Controller
{
    public function __invoke(ExportReportRequest $request, ReportingService $reportingService): BinaryFileResponse
    {
        $report = $request->validated('report');

        [$rows, $headings, $filename] = match ($report) {
            'department_pass_percentage' => [
                $reportingService->departmentPassPercentageReport()->map(fn ($item) => [
                    $item['department_id'],
                    $item['total_results'],
                    $item['passed_results'],
                    $item['pass_percentage'],
                ])->all(),
                ['department_id', 'total_results', 'passed_results', 'pass_percentage'],
                'department_pass_percentage.xlsx',
            ],
            'semester_gpa_trend' => [
                $reportingService->semesterGpaTrendReport()->map(fn ($item) => [
                    $item['semester_id'],
                    $item['semester_name'],
                    $item['average_gpa'],
                ])->all(),
                ['semester_id', 'semester_name', 'average_gpa'],
                'semester_gpa_trend.xlsx',
            ],
            default => [
                $reportingService->topFailingSubjectsReport()->map(fn ($item) => [
                    $item['subject_id'],
                    $item['subject_code'],
                    $item['subject_name'],
                    $item['referred_count'],
                ])->all(),
                ['subject_id', 'subject_code', 'subject_name', 'referred_count'],
                'top_failing_subjects.xlsx',
            ],
        };

        return Excel::download(new GenericReportExport($rows, $headings), $filename);
    }
}
