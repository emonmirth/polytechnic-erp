<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Department;
use App\Models\Session;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Mark;
use App\Services\Academic\SemesterResultGenerationService;

class TestResultEngine extends Command
{
    protected $signature = 'app:test-result-engine';
    protected $description = 'Seed test data for result engine verification';

    public function handle(SemesterResultGenerationService $resultGenerationService): int
    {
        $this->info('Starting result engine smoke test...');

        // 1. Create Department
        $dept = Department::firstOrCreate(['code' => 'CMT'], ['name' => 'Computer Science']);

        // 2. Create Session
        $session = Session::firstOrCreate(['session_year' => '2023-24'], ['is_active' => true]);

        // 3. Create Semester
        $semester = Semester::firstOrCreate(['name' => '1st', 'level' => 1], ['is_active' => true]);

        // 4. Create Student
        $student = Student::updateOrCreate(
            ['roll_no' => '123456'],
            [
                'name' => 'John Doe',
                'reg_no' => '654321',
                'department_id' => $dept->id,
                'session_id' => $session->id,
                'semester_id' => $semester->id
            ]
        );

        // 5. Create Subject
        $subject = Subject::updateOrCreate(
            ['subject_code' => '6661'],
            [
                'name' => 'Java Programming',
                'department_id' => $dept->id,
                'semester_id' => $semester->id,
                'credit' => 3.0,
                'tc_marks' => 40,
                'tf_marks' => 60,
                'pc_marks' => 50,
                'pf_marks' => 50
            ]
        );

        // 6. Create Mark
        Mark::updateOrCreate(
            [
                'student_id' => $student->id,
                'subject_id' => $subject->id,
                'semester_id' => $semester->id,
                'exam_year' => '2024'
            ],
            [
                'session_id' => $session->id,
                'tc_mark' => 35,
                'tf_mark' => 55,
                'pc_mark' => 45,
                'pf_mark' => 45,
                'is_absent' => false,
                'is_locked' => false
            ]
        );

        $resultGenerationService->generate($session->id, $semester->id);

        $this->info('Test data seeded and semester result generated successfully.');

        return self::SUCCESS;
    }
}
