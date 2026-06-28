<?php

namespace App\Filament\Resources\PodCampaignEnrollments;

use App\Filament\Resources\PodCampaignEnrollments\Pages\CreatePodCampaignEnrollment;
use App\Filament\Resources\PodCampaignEnrollments\Pages\EditPodCampaignEnrollment;
use App\Filament\Resources\PodCampaignEnrollments\Pages\ListPodCampaignEnrollments;
use App\Filament\Resources\PodCampaignEnrollments\RelationManagers\EnrollmentMailingsRelationManager;
use App\Models\PodCampaignEnrollment;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
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

class PodCampaignEnrollmentResource extends Resource
{
    protected static ?string $model = PodCampaignEnrollment::class;

    protected static ?string $navigationLabel = 'Bible Study Enrollments';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedUserPlus;

    protected static string|\UnitEnum|null $navigationGroup = 'Ministry Modules';

    protected static ?string $navigationParentItem = 'Bible Study Campaigns';

    protected static ?int $navigationSort = 14;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Enrollment')
                    ->schema([
                        Select::make('team_id')
                            ->label('Ministry Group')
                            ->relationship('team', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('campaign_id')
                            ->label('Campaign')
                            ->relationship('campaign', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('contact_id')
                            ->label('Contact')
                            ->relationship('contact', 'last_name')
                            ->getOptionLabelFromRecordUsing(fn ($record): string => $record->full_name)
                            ->searchable(['first_name', 'last_name', 'email'])
                            ->preload()
                            ->required(),
                        Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'waiting_for_reply' => 'Waiting for Reply',
                                'paused' => 'Paused',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('active')
                            ->required(),
                        DateTimePicker::make('enrolled_at')
                            ->default(now())
                            ->required(),
                        DateTimePicker::make('completed_at'),
                        DatePicker::make('paused_until'),
                        TextInput::make('current_sequence')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->required(),
                        Select::make('next_mailing_id')
                            ->label('Next Mailing')
                            ->relationship('nextMailing', 'name')
                            ->searchable()
                            ->preload(),
                        DatePicker::make('next_send_on')
                            ->label('Next Send On'),
                    ])
                    ->columns(2),
                Section::make('Reply Tracking')
                    ->schema([
                        Select::make('reply_required_by_mailing_id')
                            ->label('Reply Required By Mailing')
                            ->relationship('replyRequiredByMailing', 'name')
                            ->searchable()
                            ->preload(),
                        DateTimePicker::make('reply_required_at'),
                        DateTimePicker::make('reply_received_at'),
                    ])
                    ->columns(2)
                    ->collapsible(),
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
                TextColumn::make('campaign.name')
                    ->label('Campaign')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('contact.full_name')
                    ->label('Contact')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(['last_name', 'first_name']),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('current_sequence')
                    ->sortable(),
                TextColumn::make('next_send_on')
                    ->date()
                    ->sortable(),
                TextColumn::make('reply_required_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('team')
                    ->relationship('team', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('campaign')
                    ->relationship('campaign', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'waiting_for_reply' => 'Waiting for Reply',
                        'paused' => 'Paused',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
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
            EnrollmentMailingsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPodCampaignEnrollments::route('/'),
            'create' => CreatePodCampaignEnrollment::route('/create'),
            'edit' => EditPodCampaignEnrollment::route('/{record}/edit'),
        ];
    }
}
