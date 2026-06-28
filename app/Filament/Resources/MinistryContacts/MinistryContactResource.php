<?php

namespace App\Filament\Resources\MinistryContacts;

use App\Filament\Resources\MinistryContacts\Pages\CreateMinistryContact;
use App\Filament\Resources\MinistryContacts\Pages\EditMinistryContact;
use App\Filament\Resources\MinistryContacts\Pages\ListMinistryContacts;
use App\Filament\Resources\MinistryContacts\RelationManagers\EventsRelationManager;
use App\Filament\Resources\MinistryContacts\RelationManagers\TasksRelationManager;
use App\Models\MinistryContact;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MinistryContactResource extends Resource
{
    protected static ?string $model = MinistryContact::class;

    protected static ?string $navigationLabel = 'Contacts';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected static string|\UnitEnum|null $navigationGroup = 'People & Outreach';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Contact')
                    ->schema([
                        Select::make('team_id')
                            ->label('Ministry Group')
                            ->relationship('team', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
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
                    ])
                    ->columns(2),
                Section::make('Source')
                    ->schema([
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
                    ])
                    ->columns(2)
                    ->collapsible(),
                Section::make('Mailing Address')
                    ->schema([
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
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('team.name')
                    ->label('Ministry Group')
                    ->searchable()
                    ->sortable(),
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
                TextColumn::make('tasks_count')
                    ->label('Tasks')
                    ->counts('tasks')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('team')
                    ->relationship('team', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'do_not_contact' => 'Do Not Contact',
                        'member' => 'Member',
                    ]),
                SelectFilter::make('first_source_type')
                    ->label('First Source')
                    ->options([
                        'bible_study' => 'Bible Study',
                        'vbs' => 'VBS',
                        'pod' => 'POD',
                        'event' => 'Event',
                        'manual' => 'Manual',
                        'website' => 'Website',
                    ]),
            ])
            ->defaultSort('updated_at', 'desc')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            TasksRelationManager::class,
            EventsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMinistryContacts::route('/'),
            'create' => CreateMinistryContact::route('/create'),
            'edit' => EditMinistryContact::route('/{record}/edit'),
        ];
    }
}
