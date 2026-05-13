<?php

namespace App\Services\Academic;

use App\Models\Mark;
use App\Models\Subject;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class BulkMarksEntryService
{
    public function __construct(
        private readonly BtebGradingService $gradingService,
        private readonly AcademicActivityLogger $activityLogger,
    ) {
    }

    /**
     * @param  array<int,array<string,mixed>>  $entries
     */
    public function upsert(array $entries): int
    {
        $validator = Validator::make(['entries' => $entries], [
            'entries' => ['required', 'array', 'min:1'],
            'entries.*.student_id' => ['required', 'integer', 'exists:students,id'],
            'entries.*.subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'entries.*.semester_id' => ['required', 'integer', 'exists:semesters,id'],
            'entries.*.session_id' => ['required', 'integer', 'exists:academic_sessions,id'],
            'entries.*.exam_year' => ['required', 'digits:4'],
            'entries.*.exam_type' => ['nullable', 'string', 'max:50'],
            'entries.*.tc_mark' => ['required', 'numeric', 'min:0'],
            'entries.*.tf_mark' => ['required', 'numeric', 'min:0'],
            'entries.*.pc_mark' => ['required', 'numeric', 'min:0'],
            'entries.*.pf_mark' => ['required', 'numeric', 'min:0'],
            'entries.*.is_absent' => ['nullable', 'boolean'],
            'entries.*.attendance_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'entries.*.auto_tc_from_attendance' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return DB::transaction(function () use ($entries): int {
            $count = 0;

            foreach ($entries as $entry) {
                $subject = Subject::query()->findOrFail((int) $entry['subject_id']);

                if (($entry['auto_tc_from_attendance'] ?? false) && isset($entry['attendance_percentage'])) {
                    $entry['tc_mark'] = (int) floor(((float) $entry['attendance_percentage'] / 100) * (int) $subject->tc_marks);
                }

                $this->validateAgainstSubjectDistribution($subject, $entry);

                $mark = Mark::query()->firstOrNew([
                    'student_id' => (int) $entry['student_id'],
                    'subject_id' => (int) $entry['subject_id'],
                    'semester_id' => (int) $entry['semester_id'],
                    'exam_year' => (string) $entry['exam_year'],
                ]);

                if ($mark->exists && $mark->is_locked) {
                    continue;
                }

                $evaluated = $this->gradingService->evaluate($subject, $entry, (bool) ($entry['is_absent'] ?? false));

                $mark->fill([
                    'session_id' => (int) $entry['session_id'],
                    'exam_type' => (string) ($entry['exam_type'] ?? 'Regular'),
                    'tc_mark' => (int) $entry['tc_mark'],
                    'tf_mark' => (int) $entry['tf_mark'],
                    'pc_mark' => (int) $entry['pc_mark'],
                    'pf_mark' => (int) $entry['pf_mark'],
                    'is_absent' => (bool) ($entry['is_absent'] ?? false),
                    'attendance_percentage' => $entry['attendance_percentage'] ?? null,
                    'total_marks' => $evaluated['total_marks'],
                    'grade_point' => $evaluated['grade_point'],
                    'letter_grade' => $evaluated['letter_grade'],
                ]);

                $mark->save();
                $this->activityLogger->log('marks.updated', $mark, [
                    'student_id' => $mark->student_id,
                    'subject_id' => $mark->subject_id,
                    'semester_id' => $mark->semester_id,
                    'session_id' => $mark->session_id,
                ]);
                $count++;
            }

            return $count;
        });
    }

    /**
     * @param array<string,mixed> $entry
     */
    private function validateAgainstSubjectDistribution(Subject $subject, array $entry): void
    {
        $validator = Validator::make($entry, [
            'tc_mark' => ['max:' . (int) $subject->tc_marks],
            'tf_mark' => ['max:' . (int) $subject->tf_marks],
            'pc_mark' => ['max:' . (int) $subject->pc_marks],
            'pf_mark' => ['max:' . (int) $subject->pf_marks],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
