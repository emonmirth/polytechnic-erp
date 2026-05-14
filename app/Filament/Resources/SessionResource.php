<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SessionResource\Pages;
use App\Models\Session;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;

class SessionResource extends Resource
{
    protected static ?string $model = Session::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';
    protected static string|\UnitEnum|null $navigationGroup = 'Academic Setup';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Session Info')
                ->schema([
                    TextInput::make('session_year')->placeholder('e.g., 2023-24')->required()->unique(ignoreRecord: true),
                    Toggle::make('is_active')->default(true),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('session_year')->searchable()->sortable(),
            IconColumn::make('is_active')->boolean(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSessions::route('/'),
            'create' => Pages\CreateSession::route('/create'),
            'edit' => Pages\EditSession::route('/{record}/edit'),
        ];
    }
}
