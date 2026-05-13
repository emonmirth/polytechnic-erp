<?php

namespace App\Filament\Resources\MarkResource\Pages;

use App\Filament\Resources\MarkResource;
use App\Models\Semester;
use App\Models\Session;
use App\Models\Student;
use App\Models\Subject;
use App\Services\Academic\BulkMarksEntryService;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Validation\ValidationException;

class BulkMarksEntry extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = MarkResource::class;

    protected string $view = 'filament.resources.mark-resource.pages.bulk-marks-entry';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'entries' => [
                [
                    'exam_type' => 'Regular',
                    'exam_year' => (string) now()->year,
                    'tc_mark' => 0,
                    'tf_mark' => 0,
                    'pc_mark' => 0,
                    'pf_mark' => 0,
                    'is_absent' => false,
                ],
            ],
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Repeater::make('entries')
                    ->schema([
                        Select::make('student_id')
                            ->options(Student::query()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        Select::make('subject_id')
                            ->options(Subject::query()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        Select::make('session_id')
                            ->options(Session::query()->orderBy('session_year')->pluck('session_year', 'id'))
                            ->required(),
                        Select::make('semester_id')
                            ->options(Semester::query()->orderBy('level')->pluck('name', 'id'))
                            ->required(),
                        TextInput::make('exam_type')
                            ->required()
                            ->default('Regular'),
                        TextInput::make('exam_year')
                            ->required()
                            ->numeric(),
                        TextInput::make('tc_mark')->numeric()->required()->default(0),
                        TextInput::make('tf_mark')->numeric()->required()->default(0),
                        TextInput::make('pc_mark')->numeric()->required()->default(0),
                        TextInput::make('pf_mark')->numeric()->required()->default(0),
                        TextInput::make('attendance_percentage')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->helperText('Optional attendance percentage (0-100).'),
                        Toggle::make('auto_tc_from_attendance')
                            ->default(false)
                            ->label('Auto TC from Attendance'),
                        Toggle::make('is_absent')->default(false),
                    ])
                    ->columnSpanFull()
                    ->minItems(1)
                    ->defaultItems(1)
                    ->columns(3),
            ])
            ->statePath('data');
    }

    public function save(BulkMarksEntryService $service): void
    {
        try {
            $state = $this->form->getState();
            $saved = $service->upsert($state['entries'] ?? []);

            Notification::make()
                ->title('Bulk marks saved')
                ->body("{$saved} mark rows processed successfully.")
                ->success()
                ->send();
        } catch (ValidationException $exception) {
            Notification::make()
                ->title('Validation failed')
                ->body(collect($exception->errors())->flatten()->join(' | '))
                ->danger()
                ->send();

            throw $exception;
        }
    }
}
