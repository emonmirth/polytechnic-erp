<?php

namespace App\Console\Commands;

use App\Models\Subject;
use App\Services\Academic\BtebGradingService;
use Illuminate\Console\Command;

class TestBtebLogic extends Command
{
    protected $signature = 'app:test-bteb-logic';
    protected $description = 'Perform Acid Test on BTEB Grading Logic';

    public function handle(BtebGradingService $gradingService)
    {
        $this->info("--- Starting BTEB Logic Acid Test ---");

        $subject = new Subject([
            'tc_marks' => 30,
            'tf_marks' => 90,
            'pc_marks' => 25,
            'pf_marks' => 55,
        ]);

        // Case 1: Border Case 80% (A+)
        $marks1 = ['tc_mark' => 24, 'tf_mark' => 72, 'pc_mark' => 20, 'pf_mark' => 44]; // 160/200
        $res1 = $gradingService->evaluate($subject, $marks1);
        $this->reportCase("80% Border (A+)", $res1, 'A+', 4.00, 'PASSED');

        // Case 2: Border Case 79.5% -> 80%? No, let's test 79% (A)
        $marks2 = ['tc_mark' => 24, 'tf_mark' => 71, 'pc_mark' => 20, 'pf_mark' => 43]; // 158/200 = 79%
        $res2 = $gradingService->evaluate($subject, $marks2);
        $this->reportCase("79% Border (A)", $res2, 'A', 3.75, 'PASSED');

        // Case 3: TF Fail (Total > 40% but TF < 40%)
        // TF Max = 90. 40% = 36. 35 is fail.
        $marks3 = ['tc_mark' => 30, 'tf_mark' => 35, 'pc_mark' => 25, 'pf_mark' => 55]; // 145/200 = 72.5%
        $res3 = $gradingService->evaluate($subject, $marks3);
        $this->reportCase("TF Fail (35/90)", $res3, 'F', 0.00, 'REFERRED');

        // Case 4: PF Fail (Total > 40% but PF < 40%)
        // PF Max = 55. 40% = 22. 21 is fail.
        $marks4 = ['tc_mark' => 30, 'tf_mark' => 90, 'pc_mark' => 25, 'pf_mark' => 21]; // 166/200 = 83%
        $res4 = $gradingService->evaluate($subject, $marks4);
        $this->reportCase("PF Fail (21/55)", $res4, 'F', 0.00, 'REFERRED');

        $this->info("--- Acid Test Complete ---");
    }

    private function reportCase($name, $res, $expectedGrade, $expectedGP, $expectedStatus)
    {
        $passed = $res['letter_grade'] === $expectedGrade && 
                 (float)$res['grade_point'] === (float)$expectedGP && 
                 $res['status'] === $expectedStatus;

        $status = $passed ? "<fg=green>PASS</>" : "<fg=red>FAIL</>";
        
        $this->line("[$status] $name: Grade={$res['letter_grade']}, GP={$res['grade_point']}, Status={$res['status']}");
        
        if (!$passed) {
            $this->error("   Expected: Grade=$expectedGrade, GP=$expectedGP, Status=$expectedStatus");
        }
    }
}
