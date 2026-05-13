<?php

namespace App\Filament\Widgets;

use App\Services\Academic\ReportingService;
use Filament\Widgets\ChartWidget;

class SemesterGpaTrendWidget extends ChartWidget
{
    protected ?string $heading = 'Semester GPA Trend';

    protected function getData(): array
    {
        $rows = app(ReportingService::class)->semesterGpaTrendReport();

        return [
            'datasets' => [
                [
                    'label' => 'Average GPA',
                    'data' => $rows->pluck('average_gpa')->all(),
                ],
            ],
            'labels' => $rows->pluck('semester_name')->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
