<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Mark;
use App\Models\Result;
use App\Models\Semester;
use App\Models\Session;
use App\Models\Student;
use App\Models\Subject;
use App\Services\Academic\BtebGradingService;
use App\Services\Academic\BulkMarksEntryService;
use App\Services\Academic\SemesterResultGenerationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AcademicResultEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_bteb_grading_requires_separate_tf_and_pf_pass_marks(): void
    {
        $subject = $this->seedSubject();
        $service = app(BtebGradingService::class);

        $tfFailed = $service->evaluate($subject, [
            'tc_mark' => 40,
            'tf_mark' => 23,
            'pc_mark' => 25,
            'pf_mark' => 20,
        ]);

        $pfFailed = $service->evaluate($subject, [
            'tc_mark' => 40,
            'tf_mark' => 45,
            'pc_mark' => 25,
            'pf_mark' => 9,
        ]);

        $passed = $service->evaluate($subject, [
            'tc_mark' => 40,
            'tf_mark' => 45,
            'pc_mark' => 25,
            'pf_mark' => 20,
        ]);

        $this->assertTrue($tfFailed['theory_failed']);
        $this->assertFalse($tfFailed['practical_failed']);
        $this->assertSame('REFERRED', $tfFailed['status']);

        $this->assertFalse($pfFailed['theory_failed']);
        $this->assertTrue($pfFailed['practical_failed']);
        $this->assertSame('REFERRED', $pfFailed['status']);

        $this->assertFalse($passed['is_referred']);
        $this->assertSame('PASSED', $passed['status']);
    }

    public function test_bulk_marks_reject_values_above_subject_distribution(): void
    {
        [$student, $subject, $session, $semester] = $this->seedAcademicContext();

        $this->expectException(ValidationException::class);

        app(BulkMarksEntryService::class)->upsert([[
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'semester_id' => $semester->id,
            'session_id' => $session->id,
            'exam_year' => '2026',
            'exam_type' => 'Regular',
            'tc_mark' => 41,
            'tf_mark' => 45,
            'pc_mark' => 20,
            'pf_mark' => 20,
        ]]);
    }

    public function test_semester_result_generation_snapshots_grading_totals_and_referred_subjects(): void
    {
        [$student, $subject, $session, $semester] = $this->seedAcademicContext();

        Mark::query()->create([
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'semester_id' => $semester->id,
            'session_id' => $session->id,
            'exam_year' => '2026',
            'exam_type' => 'Regular',
            'tc_mark' => 40,
            'tf_mark' => 23,
            'pc_mark' => 25,
            'pf_mark' => 20,
        ]);

        app(SemesterResultGenerationService::class)->generate($session->id, $semester->id);

        $result = Result::query()->with('items')->firstOrFail();
        $item = $result->items->first();

        $this->assertSame('REFERRED', $result->status);
        $this->assertSame([$subject->subject_code], $result->referred_subject_codes);
        $this->assertSame(108, $result->total_marks);
        $this->assertSame('published', $result->publication_status);
        $this->assertSame('REFERRED', $item->status);
        $this->assertSame(108, $item->total_marks);
        $this->assertSame('F', $item->letter_grade);
        $this->assertEquals(0.00, (float) $item->grade_point);
    }

    /**
     * @return array{0:Student,1:Subject,2:Session,3:Semester}
     */
    private function seedAcademicContext(): array
    {
        $subject = $this->seedSubject();
        $department = $subject->department;
        $semester = $subject->semester;
        $session = Session::query()->create(['session_year' => '2025-26']);
        $student = Student::query()->create([
            'name' => 'Engine Test Student',
            'roll_no' => 'CST-2026-001',
            'reg_no' => 'REG-2026-001',
            'department_id' => $department->id,
            'session_id' => $session->id,
            'semester_id' => $semester->id,
            'shift' => 'Day',
        ]);

        return [$student, $subject, $session, $semester];
    }

    private function seedSubject(): Subject
    {
        $department = Department::query()->create(['name' => 'Computer Science', 'code' => 'CST']);
        $semester = Semester::query()->create(['name' => '1st Semester', 'level' => 1, 'shift' => 'Day']);

        return Subject::query()->create([
            'name' => 'Computer Fundamentals',
            'subject_code' => '66611',
            'department_id' => $department->id,
            'semester_id' => $semester->id,
            'credit' => 3,
            'tc_marks' => 40,
            'tf_marks' => 60,
            'pc_marks' => 25,
            'pf_marks' => 25,
        ]);
    }
}
