<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuditLogResource\Pages;
use App\Models\AuditLog;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string|\UnitEnum|null $navigationGroup = 'Reports & Analytics';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            \Filament\Forms\Components\Section::make('Audit Event Details')
                ->schema([
                    \Filament\Forms\Components\TextInput::make('event')->readOnly(),
                    \Filament\Forms\Components\TextInput::make('actor_id')->label('Actor (User ID)')->readOnly(),
                    \Filament\Forms\Components\TextInput::make('subject_type')->readOnly(),
                    \Filament\Forms\Components\TextInput::make('subject_id')->readOnly(),
                    \Filament\Forms\Components\KeyValue::make('metadata')
                        ->label('Event Metadata')
                        ->readOnly()
                        ->columnSpanFull(),
                    \Filament\Forms\Components\DateTimePicker::make('created_at')->readOnly(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->dateTime()->sortable(),
                TextColumn::make('event')->badge()->sortable()->searchable(),
                TextColumn::make('actor_id')->label('Actor ID')->sortable(),
                TextColumn::make('subject_type')->sortable()->searchable(),
                TextColumn::make('subject_id')->sortable(),
            ])
            ->filters([
                SelectFilter::make('event')
                    ->options(AuditLog::query()->distinct()->pluck('event', 'event')->toArray()),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuditLogs::route('/'),
        ];
    }
}
