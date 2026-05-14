<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ResultResource\Pages;
use App\Models\Result;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;

class ResultResource extends Resource
{
    protected static ?string $model = Result::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';
    protected static string|\UnitEnum|null $navigationGroup = 'Result Management';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            \Filament\Schemas\Components\Section::make('Result Snapshot Summary')
                ->description('These details are snapshotted at the time of result generation and are immutable when locked.')
                ->schema([
                    \Filament\Schemas\Components\Grid::make(3)->schema([
                        \Filament\Forms\Components\TextInput::make('student.name')->label('Student Name')->readOnly(),
                        \Filament\Forms\Components\TextInput::make('student.roll_no')->label('Roll No')->readOnly(),
                        \Filament\Forms\Components\TextInput::make('semester.name')->label('Semester')->readOnly(),
                    ]),
                    \Filament\Schemas\Components\Grid::make(4)->schema([
                        \Filament\Forms\Components\TextInput::make('gpa')->label('Semester GPA')->readOnly(),
                        \Filament\Forms\Components\TextInput::make('cgpa')->label('CGPA')->readOnly(),
                        \Filament\Forms\Components\TextInput::make('status')->label('Status')->readOnly(),
                        \Filament\Forms\Components\TextInput::make('publication_status')->label('Publication')->readOnly(),
                    ]),
                ]),
            \Filament\Schemas\Components\Section::make('Integrity Metadata')
                ->collapsed()
                ->schema([
                    \Filament\Forms\Components\TextInput::make('verification_token')->label('Verification Token')->readOnly(),
                    \Filament\Forms\Components\TextInput::make('snapshot_hash')->label('SHA-256 Integrity Hash')->readOnly()->columnSpanFull(),
                ]),
        ]); 
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('student.roll_no')->label('Roll')->searchable()->sortable(),
            TextColumn::make('student.name')->label('Student')->searchable(),
            TextColumn::make('semester.name')->label('Semester'),
            TextColumn::make('session.session_year')->label('Session'),
            TextColumn::make('gpa')
                ->label('GPA')
                ->badge()
                ->color(fn ($state) => floatval($state) >= 2.0 ? 'success' : 'danger'),
            TextColumn::make('status')
                ->badge()
                ->color(fn ($state) => match ($state) {
                    'PASSED' => 'success',
                    'REFERRED', 'FAILED' => 'danger',
                    default => 'warning',
                }),
            TextColumn::make('publication_status')->label('Publication')->badge(),
            TextColumn::make('is_locked')->label('Locked')->formatStateUsing(fn (bool $state): string => $state ? 'Yes' : 'No')->badge(),
        ])->actions([
            ActionGroup::make([
                Action::make('view_result_card')
                    ->label('Result Card')
                    ->icon('heroicon-o-document-text')
                    ->color('info')
                    ->modalHeading('Student Result Card')
                    ->modalWidth('4xl')
                    ->modalContent(fn (Result $record) => view('filament.pages.result-card-preview', ['result' => $record])),
                Action::make('publish')
                    ->label('Publish')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->visible(fn (Result $record): bool => !$record->is_locked && $record->publication_status !== 'published')
                    ->action(fn (Result $record) => app(\App\Services\Academic\ResultLifecycleService::class)->publish($record)),
                Action::make('mark_draft')
                    ->label('Move to Draft')
                    ->icon('heroicon-o-archive-box')
                    ->color('gray')
                    ->visible(fn (Result $record): bool => !$record->is_locked && $record->publication_status === 'published')
                    ->action(fn (Result $record) => app(\App\Services\Academic\ResultLifecycleService::class)->saveDraft($record)),
                Action::make('lock')
                    ->label('Lock')
                    ->icon('heroicon-o-lock-closed')
                    ->color('warning')
                    ->visible(fn (Result $record): bool => !$record->is_locked)
                    ->requiresConfirmation()
                    ->action(fn (Result $record) => app(\App\Services\Academic\ResultLifecycleService::class)->lock($record)),
                Action::make('unlock')
                    ->label('Unlock')
                    ->icon('heroicon-o-lock-open')
                    ->color('warning')
                    ->visible(fn (Result $record): bool => $record->is_locked)
                    ->requiresConfirmation()
                    ->action(fn (Result $record) => app(\App\Services\Academic\ResultLifecycleService::class)->unlock($record)),
                Action::make('transcript')
                    ->label('Transcript PDF')
                    ->icon('heroicon-o-qr-code')
                    ->color('primary')
                    ->visible(fn (Result $record): bool => $record->publication_status === 'published')
                    ->url(fn (Result $record): string => route('results.transcript', $record))
                    ->openUrlInNewTab(),
            ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListResults::route('/'),
        ];
    }
}
