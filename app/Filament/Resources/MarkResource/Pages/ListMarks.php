<?php

namespace App\Filament\Resources\MarkResource\Pages;

use App\Filament\Resources\MarkResource;
use App\Services\Academic\Imports\ExcelMarksImportService;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Validation\ValidationException;

class ListMarks extends ListRecords
{
    protected static string $resource = MarkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('bulk_entry')
                ->label('Bulk Marks Entry')
                ->icon('heroicon-o-table-cells')
                ->url($this->getResource()::getUrl('bulk')),
            Actions\Action::make('import_excel')
                ->label('Import Excel')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('warning')
                ->form([
                    FileUpload::make('file')
                        ->required()
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                            'text/csv',
                        ]),
                ])
                ->action(function (array $data, ExcelMarksImportService $excelMarksImportService): void {
                    try {
                        $file = $data['file'] ?? null;
                        if (!$file) {
                            Notification::make()->title('No file selected')->danger()->send();
                            return;
                        }

                        $count = $excelMarksImportService->import($file);
                        Notification::make()->title("Imported {$count} marks")->success()->send();
                    } catch (ValidationException $exception) {
                        Notification::make()
                            ->title('Import failed')
                            ->body(collect($exception->errors())->flatten()->join(' | '))
                            ->danger()
                            ->send();
                    }
                }),
            Actions\CreateAction::make(),
        ];
    }
}
