<?php

namespace App\Services\Academic;

use App\Models\Subject;

class BtebGradingService
{
    public function evaluate(Subject $subject, array $marks, bool $isAbsent = false): array
    {
        if ($isAbsent) {
            return [
                'total_marks' => 0,
                'grade_point' => 0.00,
                'letter_grade' => 'F',
                'percentage' => 0.0,
                'theory_failed' => true,
                'practical_failed' => true,
                'is_referred' => true,
                'status' => 'REFERRED',
            ];
        }

        $tc = (int) ($marks['tc_mark'] ?? 0);
        $tf = (int) ($marks['tf_mark'] ?? 0);
        $pc = (int) ($marks['pc_mark'] ?? 0);
        $pf = (int) ($marks['pf_mark'] ?? 0);

        $theoryMax = (int) $subject->tc_marks + (int) $subject->tf_marks;
        $practicalMax = (int) $subject->pc_marks + (int) $subject->pf_marks;
        $totalMax = $theoryMax + $practicalMax;

        $theoryObtained = $tc + $tf;
        $practicalObtained = $pc + $pf;
        $totalObtained = $theoryObtained + $practicalObtained;

        $theoryFailed = false;
        if ((int) $subject->tf_marks > 0 && $tf < ceil($subject->tf_marks * 0.40)) {
            $theoryFailed = true;
        }
        if ($theoryMax > 0 && $theoryObtained < ceil($theoryMax * 0.40)) {
            $theoryFailed = true;
        }

        $practicalFailed = false;
        if ((int) $subject->pf_marks > 0 && $pf < ceil($subject->pf_marks * 0.40)) {
            $practicalFailed = true;
        }
        if ($practicalMax > 0 && $practicalObtained < ceil($practicalMax * 0.40)) {
            $practicalFailed = true;
        }

        $percentage = $totalMax > 0 ? ($totalObtained / $totalMax) * 100 : 0.0;
        $overallFailed = $percentage < 40;

        $isReferred = $theoryFailed || $practicalFailed || $overallFailed;

        [$gradePoint, $letterGrade] = $isReferred
            ? [0.00, 'F']
            : $this->mapGradeLetterAndPoint($percentage);

        return [
            'total_marks' => $totalObtained,
            'grade_point' => $gradePoint,
            'letter_grade' => $letterGrade,
            'percentage' => round($percentage, 2),
            'theory_failed' => $theoryFailed,
            'practical_failed' => $practicalFailed,
            'is_referred' => $isReferred,
            'status' => $isReferred ? 'REFERRED' : 'PASSED',
        ];
    }

    public function mapGradeLetterAndPoint(float $percentage): array
    {
        return match (true) {
            $percentage >= 80 => [4.00, 'A+'],
            $percentage >= 75 => [3.75, 'A'],
            $percentage >= 70 => [3.50, 'A-'],
            $percentage >= 65 => [3.25, 'B+'],
            $percentage >= 60 => [3.00, 'B'],
            $percentage >= 55 => [2.75, 'B-'],
            $percentage >= 50 => [2.50, 'C+'],
            $percentage >= 45 => [2.25, 'C'],
            $percentage >= 40 => [2.00, 'D'],
            default => [0.00, 'F'],
        };
    }
}
