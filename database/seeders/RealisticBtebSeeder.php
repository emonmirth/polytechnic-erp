<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Mark;
use App\Models\Semester;
use App\Models\Session;
use App\Models\Student;
use App\Models\Subject;
use App\Services\Academic\SemesterResultGenerationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RealisticBtebSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $departments = [
                ['name' => 'Computer Science and Technology', 'code' => 'CST'],
                ['name' => 'Civil Technology', 'code' => 'CIVIL'],
                ['name' => 'Power Technology', 'code' => 'POWER'],
            ];

            $sessions = ['2022-23', '2023-24'];

            $semesterDefinitions = [
                ['name' => '1st Semester', 'level' => 1, 'shift' => 'Day'],
                ['name' => '2nd Semester', 'level' => 2, 'shift' => 'Day'],
                ['name' => '3rd Semester', 'level' => 3, 'shift' => 'Day'],
                ['name' => '4th Semester', 'level' => 4, 'shift' => 'Day'],
            ];

            $subjectBank = [
                'CST' => [
                    1 => [['code' => '66611', 'name' => 'Computer Fundamentals'], ['code' => '66612', 'name' => 'Electrical Basic'], ['code' => '66613', 'name' => 'Mathematics-I']],
                    2 => [['code' => '66621', 'name' => 'Structured Programming'], ['code' => '66622', 'name' => 'Data Communication'], ['code' => '66623', 'name' => 'Mathematics-II']],
                    3 => [['code' => '66631', 'name' => 'Object Oriented Programming'], ['code' => '66632', 'name' => 'Web Design'], ['code' => '66633', 'name' => 'Database Management System']],
                    4 => [['code' => '66641', 'name' => 'Operating System'], ['code' => '66642', 'name' => 'Java Programming'], ['code' => '66643', 'name' => 'Software Engineering']],
                ],
                'CIVIL' => [
                    1 => [['code' => '76411', 'name' => 'Civil Engineering Materials'], ['code' => '76412', 'name' => 'Engineering Drawing'], ['code' => '76413', 'name' => 'Mathematics-I']],
                    2 => [['code' => '76421', 'name' => 'Surveying-I'], ['code' => '76422', 'name' => 'Construction Process'], ['code' => '76423', 'name' => 'Mechanics']],
                    3 => [['code' => '76431', 'name' => 'Surveying-II'], ['code' => '76432', 'name' => 'Hydraulics'], ['code' => '76433', 'name' => 'Structural Mechanics']],
                    4 => [['code' => '76441', 'name' => 'Concrete Technology'], ['code' => '76442', 'name' => 'Estimation and Costing'], ['code' => '76443', 'name' => 'Soil Mechanics']],
                ],
                'POWER' => [
                    1 => [['code' => '76811', 'name' => 'Basic Electricity'], ['code' => '76812', 'name' => 'Workshop Practice'], ['code' => '76813', 'name' => 'Mathematics-I']],
                    2 => [['code' => '76821', 'name' => 'Electrical Circuit'], ['code' => '76822', 'name' => 'Electronics-I'], ['code' => '76823', 'name' => 'Physics']],
                    3 => [['code' => '76831', 'name' => 'Electrical Machines-I'], ['code' => '76832', 'name' => 'Instrumentation'], ['code' => '76833', 'name' => 'Power Plant Engineering']],
                    4 => [['code' => '76841', 'name' => 'Electrical Machines-II'], ['code' => '76842', 'name' => 'Transmission and Distribution'], ['code' => '76843', 'name' => 'Renewable Energy Technology']],
                ],
            ];

            $sessionModels = collect($sessions)->mapWithKeys(fn ($sessionYear) => [
                $sessionYear => Session::query()->updateOrCreate(
                    ['session_year' => $sessionYear],
                    ['is_active' => true],
                ),
            ]);

            $semesterModels = collect($semesterDefinitions)->mapWithKeys(fn ($semester) => [
                $semester['level'] => Semester::query()->updateOrCreate(
                    ['name' => $semester['name']],
                    ['level' => $semester['level'], 'shift' => $semester['shift'], 'is_active' => true],
                ),
            ]);

            $departmentModels = collect($departments)->mapWithKeys(function (array $dept) use ($subjectBank, $semesterModels) {
                $department = Department::query()->updateOrCreate(
                    ['code' => $dept['code']],
                    ['name' => $dept['name'], 'is_active' => true],
                );

                foreach ($subjectBank[$dept['code']] as $semesterLevel => $subjects) {
                    $semester = $semesterModels[$semesterLevel];
                    foreach ($subjects as $subject) {
                        Subject::query()->updateOrCreate(
                            ['department_id' => $department->id, 'subject_code' => $subject['code']],
                            [
                                'semester_id' => $semester->id,
                                'name' => $subject['name'],
                                'credit' => 3.0,
                                'tc_marks' => 40,
                                'tf_marks' => 60,
                                'pc_marks' => 25,
                                'pf_marks' => 25,
                            ],
                        );
                    }
                }

                return [$dept['code'] => $department];
            });

            $studentCounter = 1;
            foreach ($departmentModels as $deptCode => $department) {
                foreach ($sessionModels as $sessionYear => $session) {
                    foreach ($semesterModels as $semesterLevel => $semester) {
                        for ($i = 1; $i <= 8; $i++) {
                            $roll = sprintf('%s-%s-%03d', $deptCode, str_replace('-', '', $sessionYear), $studentCounter);

                            $student = Student::query()->updateOrCreate(
                                ['roll_no' => $roll],
                                [
                                    'name' => 'Student ' . $studentCounter,
                                    'reg_no' => 'REG-' . str_pad((string) (700000 + $studentCounter), 6, '0', STR_PAD_LEFT),
                                    'department_id' => $department->id,
                                    'session_id' => $session->id,
                                    'semester_id' => $semester->id,
                                    'shift' => 'Day',
                                    'admission_date' => now()->subMonths(rand(8, 36))->toDateString(),
                                    'phone' => '017' . rand(10000000, 99999999),
                                    'email' => strtolower($roll) . '@poly.edu.bd',
                                    'is_active' => true,
                                ],
                            );

                            $subjects = Subject::query()
                                ->where('department_id', $department->id)
                                ->where('semester_id', $semester->id)
                                ->get();

                            foreach ($subjects as $subject) {
                                $failed = rand(1, 100) <= 20;
                                $tf = $failed ? rand(10, 22) : rand(25, 55);
                                $pf = $failed ? rand(8, 9) : rand(10, 23);

                                Mark::query()->updateOrCreate(
                                    [
                                        'student_id' => $student->id,
                                        'subject_id' => $subject->id,
                                        'semester_id' => $semester->id,
                                        'exam_year' => '2026',
                                    ],
                                    [
                                        'session_id' => $session->id,
                                        'exam_type' => 'Regular',
                                        'tc_mark' => rand(16, 38),
                                        'tf_mark' => min($tf, (int) $subject->tf_marks),
                                        'pc_mark' => rand(10, 24),
                                        'pf_mark' => min($pf, (int) $subject->pf_marks),
                                        'attendance_percentage' => rand(60, 100),
                                        'is_absent' => false,
                                        'is_locked' => false,
                                    ],
                                );
                            }

                            $studentCounter++;
                        }
                    }
                }
            }
        });

        $resultGenerator = app(SemesterResultGenerationService::class);

        foreach (Session::query()->get() as $session) {
            foreach (Semester::query()->get() as $semester) {
                $resultGenerator->generate($session->id, $semester->id, 'published');
            }
        }
    }
}
