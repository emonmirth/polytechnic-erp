<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubjectResource\Pages;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Columns\TextColumn;

class SubjectResource extends Resource
{
    protected static ?string $model = Subject::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-book-open';
    protected static string|\UnitEnum|null $navigationGroup = 'Academic Setup';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['department', 'semester']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(3)->schema([
                Section::make('Basic Info')
                    ->schema([
                        Select::make('department_id')->relationship('department', 'name')->required()->searchable(),
                        Select::make('semester_id')->relationship('semester', 'name')->required()->searchable(),
                        TextInput::make('name')->required(),
                        TextInput::make('subject_code')->label('Subject Code')->required(),
                        TextInput::make('credit')->numeric()->step(0.5)->minValue(0)->maxValue(9.9)->required(),
                    ])->columnSpan(2),
                
                Section::make('BTEB Marks Distribution')
                    ->schema([
                        TextInput::make('tc_marks')->label('TC Marks')->numeric()->minValue(0)->maxValue(999)->default(0)->required(),
                        TextInput::make('tf_marks')->label('TF Marks')->numeric()->minValue(0)->maxValue(999)->default(0)->required(),
                        TextInput::make('pc_marks')->label('PC Marks')->numeric()->minValue(0)->maxValue(999)->default(0)->required(),
                        TextInput::make('pf_marks')->label('PF Marks')->numeric()->minValue(0)->maxValue(999)->default(0)->required(),
                    ])->columnSpan(1),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('subject_code')->label('Code')->searchable()->sortable(),
            TextColumn::make('name')->searchable(),
            TextColumn::make('department.code')->sortable(),
            TextColumn::make('semester.name')->sortable(),
            TextColumn::make('credit')->badge(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubjects::route('/'),
            'create' => Pages\CreateSubject::route('/create'),
            'edit' => Pages\EditSubject::route('/{record}/edit'),
        ];
    }
}
