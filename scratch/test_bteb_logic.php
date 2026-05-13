<?php

use App\Models\Subject;
use App\Services\Academic\BtebGradingService;

// Mock Subject
$subject = new Subject([
    'tc_marks' => 30,
    'tf_marks' => 90,
    'pc_marks' => 25,
    'pf_marks' => 55,
]);

$gradingService = app(BtebGradingService::class);

echo "--- BTEB Logic Testing ---\n";

// Test Case 1: Border Case 80% (A+)
$marks1 = ['tc_mark' => 24, 'tf_mark' => 72, 'pc_mark' => 20, 'pf_mark' => 44]; // Total: 160/200 = 80%
$res1 = $gradingService->evaluate($subject, $marks1);
echo "Case 1 (80%): {$res1['letter_grade']} (GP: {$res1['grade_point']}) - Status: {$res1['status']}\n";

// Test Case 2: Border Case 79% (A)
$marks2 = ['tc_mark' => 24, 'tf_mark' => 71, 'pc_mark' => 20, 'pf_mark' => 43]; // Total: 158/200 = 79%
$res2 = $gradingService->evaluate($subject, $marks2);
echo "Case 2 (79%): {$res2['letter_grade']} (GP: {$res2['grade_point']}) - Status: {$res2['status']}\n";

// Test Case 3: TF Fail (Total > 40% but TF < 40%)
// TF Max = 90. 40% of 90 is 36. Let's give 35.
$marks3 = ['tc_mark' => 30, 'tf_mark' => 35, 'pc_mark' => 25, 'pf_mark' => 55]; // Total: 145/200 = 72.5%
$res3 = $gradingService->evaluate($subject, $marks3);
echo "Case 3 (TF 35/90): {$res3['letter_grade']} (GP: {$res3['grade_point']}) - Status: {$res3['status']} (Theory Failed: " . ($res3['theory_failed'] ? 'Yes' : 'No') . ")\n";

// Test Case 4: PF Fail (Total > 40% but PF < 40%)
// PF Max = 55. 40% of 55 is 22. Let's give 21.
$marks4 = ['tc_mark' => 30, 'tf_mark' => 90, 'pc_mark' => 25, 'pf_mark' => 21]; // Total: 166/200 = 83%
$res4 = $gradingService->evaluate($subject, $marks4);
echo "Case 4 (PF 21/55): {$res4['letter_grade']} (GP: {$res4['grade_point']}) - Status: {$res4['status']} (Practical Failed: " . ($res4['practical_failed'] ? 'Yes' : 'No') . ")\n";

echo "--- Testing Complete ---\n";
