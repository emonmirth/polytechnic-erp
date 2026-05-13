<?php

namespace App\Services\Academic\Imports;

use App\Models\Student;
use App\Models\Subject;
use App\Services\Academic\BulkMarksEntryService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class ExcelMarksImportService
{
    public function __construct(
        private readonly BulkMarksEntryService $bulkMarksEntryService,
    ) {
    }

    public function import(UploadedFile|string $file): int
    {
        $source = $file;
        if (is_string($file)) {
            $source = Storage::disk('public')->path($file);
        }

        $rows = Excel::toArray([], $source)[0] ?? [];

        if (empty($rows)) {
            throw ValidationException::withMessages([
                'file' => 'The uploaded file has no data rows.',
            ]);
        }

        $header = array_map(static fn ($value) => strtolower(trim((string) $value)), (array) array_shift($rows));

        $requiredHeaders = [
            'roll_no',
            'subject_code',
            'semester_id',
            'session_id',
            'exam_year',
            'tc_mark',
            'tf_mark',
            'pc_mark',
            'pf_mark',
        ];

        foreach ($requiredHeaders as $requiredHeader) {
            if (!in_array($requiredHeader, $header, true)) {
                throw ValidationException::withMessages([
                    'file' => "Missing required column: {$requiredHeader}",
                ]);
            }
        }

        $entries = [];

        foreach ($rows as $index => $row) {
            $rowData = [];
            foreach ($header as $columnIndex => $columnName) {
                $rowData[$columnName] = $row[$columnIndex] ?? null;
            }

            if (empty($rowData['roll_no']) || empty($rowData['subject_code'])) {
                continue;
            }

            $student = Student::query()->where('roll_no', (string) $rowData['roll_no'])->first();
            $subject = Subject::query()->where('subject_code', (string) $rowData['subject_code'])->first();

            if (!$student || !$subject) {
                throw ValidationException::withMessages([
                    'file' => 'Student or subject not found at row ' . ($index + 2) . '.',
                ]);
            }

            $entry = [
                'student_id' => $student->id,
                'subject_id' => $subject->id,
                'semester_id' => (int) $rowData['semester_id'],
                'session_id' => (int) $rowData['session_id'],
                'exam_type' => (string) ($rowData['exam_type'] ?? 'Regular'),
                'exam_year' => (string) $rowData['exam_year'],
                'tc_mark' => (float) ($rowData['tc_mark'] ?? 0),
                'tf_mark' => (float) ($rowData['tf_mark'] ?? 0),
                'pc_mark' => (float) ($rowData['pc_mark'] ?? 0),
                'pf_mark' => (float) ($rowData['pf_mark'] ?? 0),
                'is_absent' => filter_var($rowData['is_absent'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'attendance_percentage' => $rowData['attendance_percentage'] !== null ? (float) $rowData['attendance_percentage'] : null,
                'auto_tc_from_attendance' => filter_var($rowData['auto_tc_from_attendance'] ?? false, FILTER_VALIDATE_BOOLEAN),
            ];

            $validator = Validator::make($entry, [
                'semester_id' => ['required', 'integer', 'exists:semesters,id'],
                'session_id' => ['required', 'integer', 'exists:academic_sessions,id'],
                'exam_year' => ['required', 'digits:4'],
            ]);

            if ($validator->fails()) {
                throw ValidationException::withMessages([
                    'file' => 'Invalid row at line ' . ($index + 2) . ': ' . $validator->errors()->first(),
                ]);
            }

            $entries[] = $entry;
        }

        return $this->bulkMarksEntryService->upsert($entries);
    }
}
