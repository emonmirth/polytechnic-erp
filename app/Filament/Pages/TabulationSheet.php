<?php

namespace App\Filament\Pages;

use App\Models\Department;
use App\Models\Semester;
use App\Models\Session;
use App\Services\TabulationService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Actions\Action;

class TabulationSheet extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-table-cells';
    protected static string|\UnitEnum|null $navigationGroup = 'Result Management';
    protected string $view = 'filament.pages.tabulation-sheet';

    public ?array $data = [];
    public ?array $reportData = null;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Filter Tabulation Sheet')
                    ->schema([
                        Select::make('department_id')
                            ->label('Department')
                            ->options(Department::all()->pluck('name', 'id'))
                            ->required()
                            ->searchable(),
                        Select::make('session_id')
                            ->label('Session')
                            ->options(Session::all()->pluck('session_year', 'id'))
                            ->required(),
                        Select::make('semester_id')
                            ->label('Semester')
                            ->options(Semester::all()->pluck('name', 'id'))
                            ->required(),
                    ])->columns(3),
            ])
            ->statePath('data');
    }

    public function generateReport()
    {
        $formData = $this->form->getState();
        
        $service = new TabulationService();
        $this->reportData = $service->getSemesterReport(
            $formData['department_id'],
            $formData['semester_id'],
            $formData['session_id']
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generate')
                ->label('Load Tabulation Sheet')
                ->action('generateReport')
        ];
    }
}
