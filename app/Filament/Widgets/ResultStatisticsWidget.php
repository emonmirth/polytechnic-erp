<?php

namespace App\Filament\Widgets;

use App\Services\Academic\ReportingService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ResultStatisticsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $summary = app(ReportingService::class)->resultSummaryStats();

        return [
            Stat::make('Total Results', (string) $summary['total_results']),
            Stat::make('Published Results', (string) $summary['published_results']),
            Stat::make('Locked Results', (string) $summary['locked_results']),
            Stat::make('Average GPA', number_format((float) $summary['average_gpa'], 2)),
        ];
    }
}
