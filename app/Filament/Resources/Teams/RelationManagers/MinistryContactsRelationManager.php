<?php

namespace App\Filament\Resources\Teams\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MinistryContactsRelationManager extends RelationManager
{
    protected static string $relationship = 'ministryContacts';

    protected static ?string $title = 'Contacts';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('external_key')
                    ->label('External Key')
                    ->maxLength(255),
                Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'do_not_contact' => 'Do Not Contact',
                        'member' => 'Member',
                    ])
                    ->default('active')
                    ->required(),
                Select::make('first_source_type')
                    ->label('First Source')
                    ->options([
                        'bible_study' => 'Bible Study',
                        'vbs' => 'VBS',
                        'pod' => 'POD',
                        'event' => 'Event',
                        'manual' => 'Manual',
                        'website' => 'Website',
                    ])
                    ->searchable(),
                TextInput::make('first_source_name')
                    ->maxLength(255),
                DateTimePicker::make('first_contacted_at'),
                TextInput::make('first_name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('last_name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('organization')
                    ->maxLength(255),
                TextInput::make('email')
                    ->email()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->tel()
                    ->maxLength(255),
                TextInput::make('address1')
                    ->label('Address 1')
                    ->required()
                    ->maxLength(255),
                TextInput::make('address2')
                    ->label('Address 2')
                    ->maxLength(255),
                TextInput::make('city')
                    ->required()
                    ->maxLength(255),
                TextInput::make('state')
                    ->required()
                    ->maxLength(255),
                TextInput::make('zip')
                    ->required()
                    ->maxLength(20),
                TextInput::make('country')
                    ->required()
                    ->default('US')
                    ->maxLength(2),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('last_name')
            ->columns([
                TextColumn::make('full_name')
                    ->label('Name')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(['last_name', 'first_name']),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('first_source_type')
                    ->label('First Source')
                    ->badge()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('city')
                    ->searchable(),
                TextColumn::make('state')
                    ->searchable(),
                TextColumn::make('events_count')
                    ->label('Timeline')
                    ->counts('events')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
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
