<?php

namespace App\Filament\Resources\ResultResource\Pages;

use App\Filament\Resources\ResultResource;
use App\Services\Academic\SemesterResultGenerationService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListResults extends ListRecords
{
    protected static string $resource = ResultResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generate_results')
                ->label('Generate Semester Results')
                ->icon('heroicon-o-cpu-chip')
                ->color('primary')
                ->form([
                    Select::make('session_id')
                        ->relationship('session', 'session_year')
                        ->required(),
                    Select::make('semester_id')
                        ->relationship('semester', 'name')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $service = app(SemesterResultGenerationService::class);
                    $service->generate($data['session_id'], $data['semester_id']);

                    Notification::make()
                        ->title("Results Generated Successfully!")
                        ->success()
                        ->send();
                }),
        ];
    }
}
