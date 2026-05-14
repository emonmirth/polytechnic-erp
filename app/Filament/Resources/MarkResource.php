<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MarkResource\Pages;
use App\Models\Mark;
use App\Models\Subject;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MarkResource extends Resource
{
    protected static ?string $model = Mark::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-pencil-square';

    protected static string|\UnitEnum|null $navigationGroup = 'Result Management';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['student', 'subject', 'semester', 'session']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Academic Details')
                ->description('Choose student, subject, semester, and exam context for this mark entry.')
                ->schema([
                    Select::make('student_id')
                        ->relationship('student', 'name')
                        ->searchable()
                        ->preload()
                        ->helperText('Select the enrolled student for this record.')
                        ->required(),
                    Select::make('subject_id')
                        ->relationship('subject', 'name')
                        ->searchable()
                        ->preload()
                        ->live()
                        ->helperText('Mark distribution will appear after subject selection.')
                        ->required(),
                    Select::make('session_id')
                        ->relationship('session', 'session_year')
                        ->required(),
                    Select::make('semester_id')
                        ->relationship('semester', 'name')
                        ->required(),
                    TextInput::make('exam_type')
                        ->default('Regular')
                        ->required()
                        ->maxLength(50),
                    TextInput::make('exam_year')
                        ->numeric()
                        ->default((string) now()->year)
                        ->required(),
                ])->columns(3),
            Section::make('BTEB Distribution Reference')
                ->hidden(fn ($get) => !$get('subject_id'))
                ->schema([
                    Placeholder::make('distribution')
                        ->label('Selected Subject Distribution')
                        ->content(function ($get): string {
                            $subject = Subject::query()->find($get('subject_id'));

                            if (!$subject) {
                                return 'Please select a valid subject.';
                            }

                            return "Theory (TC: {$subject->tc_marks}, TF: {$subject->tf_marks}) | " .
                                "Practical (PC: {$subject->pc_marks}, PF: {$subject->pf_marks}) | " .
                                "Total: {$subject->getTotalMarks()}";
                        }),
                ]),
            Section::make('Mark Entry')
                ->description('Enter marks strictly within subject full marks.')
                ->schema([
                    TextInput::make('tc_mark')
                        ->label('Theory Continuous')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->required(),
                    TextInput::make('tf_mark')
                        ->label('Theory Final')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->required(),
                    TextInput::make('pc_mark')
                        ->label('Practical Continuous')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->required(),
                    TextInput::make('pf_mark')
                        ->label('Practical Final')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->required(),
                    TextInput::make('attendance_percentage')
                        ->label('Attendance %')
                        ->helperText('Optional. If you use bulk import with auto-attendance mode, TC can be auto-derived.')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100),
                    Toggle::make('is_absent')
                        ->label('Student Absent')
                        ->default(false),
                    Toggle::make('is_locked')
                        ->label('Lock Mark Entry')
                        ->helperText('Locked marks cannot be edited later.')
                        ->default(false),
                ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student.roll_no')->label('Roll')->searchable()->sortable(),
                TextColumn::make('student.name')->label('Student')->searchable()->sortable(),
                TextColumn::make('subject.subject_code')->label('Subject Code')->searchable(),
                TextColumn::make('semester.name')->label('Semester')->sortable(),
                TextColumn::make('session.session_year')->label('Session')->sortable(),
                TextColumn::make('total_marks')->label('Total')->sortable(),
                TextColumn::make('grade_point')
                    ->label('GP')
                    ->badge()
                    ->color(fn ($state) => (float) $state > 0 ? 'success' : 'danger')
                    ->sortable(),
                TextColumn::make('letter_grade')->label('Grade')->badge(),
                IconColumn::make('is_locked')->label('Locked')->boolean(),
            ])
            ->filters([
                SelectFilter::make('semester_id')->relationship('semester', 'name')->label('Semester'),
                SelectFilter::make('session_id')->relationship('session', 'session_year')->label('Session'),
                SelectFilter::make('is_locked')
                    ->options(['1' => 'Locked', '0' => 'Unlocked'])
                    ->label('Lock Status'),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMarks::route('/'),
            'bulk' => Pages\BulkMarksEntry::route('/bulk-entry'),
            'create' => Pages\CreateMark::route('/create'),
            'edit' => Pages\EditMark::route('/{record}/edit'),
        ];
    }
}
