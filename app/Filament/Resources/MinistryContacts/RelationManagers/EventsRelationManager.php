<?php

namespace App\Filament\Resources\MinistryContacts\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EventsRelationManager extends RelationManager
{
    protected static string $relationship = 'events';

    protected static ?string $title = 'Timeline';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->options([
                        'bible_study_request' => 'Bible Study Request',
                        'vbs_registration' => 'VBS Registration',
                        'pod_delivery' => 'POD Delivery',
                        'phone_call' => 'Phone Call',
                        'email' => 'Email',
                        'visit' => 'Visit',
                        'note' => 'Note',
                        'status_change' => 'Status Change',
                    ])
                    ->searchable()
                    ->required(),
                Select::make('source')
                    ->options([
                        'bible_studies' => 'Bible Studies',
                        'vbs' => 'VBS',
                        'pod' => 'POD',
                        'manual' => 'Manual',
                        'membership' => 'Membership',
                    ])
                    ->searchable(),
                TextInput::make('source_label')
                    ->maxLength(255),
                DateTimePicker::make('occurred_at')
                    ->default(now())
                    ->required(),
                Textarea::make('summary')
                    ->rows(3)
                    ->columnSpanFull(),
                Textarea::make('notes')
                    ->rows(6)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('summary')
            ->columns([
                TextColumn::make('occurred_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->sortable(),
                TextColumn::make('source')
                    ->badge()
                    ->sortable(),
                TextColumn::make('source_label')
                    ->searchable(),
                TextColumn::make('summary')
                    ->searchable()
                    ->limit(80),
            ])
            ->defaultSort('occurred_at', 'desc')
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
