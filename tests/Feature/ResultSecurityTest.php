<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Result;
use App\Models\ResultItem;
use App\Models\Semester;
use App\Models\Session;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Services\Academic\TranscriptService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ResultSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_signed_verification_requires_valid_signature_and_token(): void
    {
        [$result] = $this->seedMinimalResult();
        $service = app(TranscriptService::class);
        $service->ensureSnapshotHash($result);

        $validUrl = $service->buildVerificationUrl($result);
        $this->get($validUrl)->assertOk();

        $invalidUrl = URL::route('verification.result', [
            'result' => $result->id,
            'token' => 'invalid-token',
        ]);

        $this->get($invalidUrl)->assertForbidden();
    }

    public function test_locked_result_prevents_core_field_updates(): void
    {
        [$result] = $this->seedMinimalResult();
        $result->update(['is_locked' => true]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $result->update(['gpa' => 3.50]);
    }

    public function test_transcript_route_requires_authentication(): void
    {
        [$result] = $this->seedMinimalResult();
        $this->get(route('results.transcript', $result))->assertRedirect();

        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->get(route('results.transcript', $result));

        $this->assertTrue(in_array($response->status(), [200, 422], true));
    }

    /**
     * @return array{0:Result,1:Student}
     */
    private function seedMinimalResult(): array
    {
        $department = Department::query()->create(['name' => 'Test Department', 'code' => 'TST']);
        $session = Session::query()->create(['session_year' => '2024-25']);
        $semester = Semester::query()->create(['name' => '1st Semester', 'level' => 1, 'shift' => 'Day']);

        $student = Student::query()->create([
            'name' => 'Demo Student',
            'roll_no' => 'TST-001',
            'reg_no' => 'REG-001',
            'department_id' => $department->id,
            'session_id' => $session->id,
            'semester_id' => $semester->id,
            'shift' => 'Day',
        ]);

        $subject = Subject::query()->create([
            'name' => 'Demo Subject',
            'subject_code' => 'SUB101',
            'department_id' => $department->id,
            'semester_id' => $semester->id,
            'credit' => 3,
            'tc_marks' => 40,
            'tf_marks' => 60,
            'pc_marks' => 25,
            'pf_marks' => 25,
        ]);

        $result = Result::query()->create([
            'student_id' => $student->id,
            'semester_id' => $semester->id,
            'session_id' => $session->id,
            'gpa' => 3.00,
            'cgpa' => 3.00,
            'total_marks' => 120,
            'status' => 'PASSED',
            'publication_status' => 'published',
            'verification_token' => 'token-123',
        ]);

        ResultItem::query()->create([
            'result_id' => $result->id,
            'subject_id' => $subject->id,
            'subject_name_snapshot' => 'Demo Subject',
            'subject_code_snapshot' => 'SUB101',
            'credit_snapshot' => 3,
            'tc_mark' => 32,
            'tf_mark' => 45,
            'pc_mark' => 20,
            'pf_mark' => 22,
            'total_marks' => 119,
            'letter_grade' => 'B+',
            'grade_point' => 3.25,
            'status' => 'PASSED',
        ]);

        return [$result->fresh(), $student];
    }
}
