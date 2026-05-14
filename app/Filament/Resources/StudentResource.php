<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentResource\Pages;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';
    protected static string|\UnitEnum|null $navigationGroup = 'Student Management';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['department', 'currentSemester', 'session']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Personal Information')
                ->schema([
                    TextInput::make('name')->required(),
                    TextInput::make('email')->email()->unique(ignoreRecord: true),
                    TextInput::make('phone'),
                ])->columns(3),

            Section::make('Academic Registration')
                ->schema([
                    TextInput::make('roll_no')->label('Roll Number')->required()->unique(ignoreRecord: true),
                    TextInput::make('reg_no')->label('Registration Number')->required()->unique(ignoreRecord: true),
                    Select::make('department_id')->relationship('department', 'name')->required()->searchable(),
                    Select::make('session_id')->relationship('session', 'session_year')->required()->searchable(),
                    Select::make('semester_id')->label('Current Semester')->relationship('currentSemester', 'name')->required()->searchable(),
                    Select::make('shift')->options(['Morning' => 'Morning', 'Day' => 'Day'])->default('Morning'),
                    DatePicker::make('admission_date'),
                ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('roll_no')->label('Roll')->searchable()->sortable(),
            TextColumn::make('name')->searchable(),
            TextColumn::make('department.code')->label('Dept')->sortable(),
            TextColumn::make('currentSemester.name')->label('Semester')->sortable(),
            TextColumn::make('session.session_year')->label('Session')->sortable(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }
}
