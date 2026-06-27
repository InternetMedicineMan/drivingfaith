<?php

namespace App\Filament\Resources\PodCampaignMailings;

use App\Filament\Resources\PodCampaignMailings\Pages\CreatePodCampaignMailing;
use App\Filament\Resources\PodCampaignMailings\Pages\EditPodCampaignMailing;
use App\Filament\Resources\PodCampaignMailings\Pages\ListPodCampaignMailings;
use App\Models\PodCampaignMailing;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PodCampaignMailingResource extends Resource
{
    protected static ?string $model = PodCampaignMailing::class;

    protected static ?string $navigationLabel = 'POD Mailings';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static string|\UnitEnum|null $navigationGroup = 'Ministry Modules';

    protected static ?string $navigationParentItem = 'Bible Study Campaigns';

    protected static ?int $navigationSort = 12;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Mailing')
                    ->schema([
                        Select::make('campaign_id')
                            ->label('Campaign')
                            ->relationship('campaign', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('sequence')
                            ->numeric()
                            ->required()
                            ->minValue(1),
                        TextInput::make('delay_days_after_previous')
                            ->label('Delay Days')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'active' => 'Active',
                                'paused' => 'Paused',
                                'archived' => 'Archived',
                            ])
                            ->default('draft')
                            ->required(),
                        Toggle::make('pause_until_reply')
                            ->label('Pause Until Reply')
                            ->inline(false),
                        Select::make('cover_letter_template_id')
                            ->label('Cover Letter Template')
                            ->relationship('coverLetterTemplate', 'name', fn ($query) => $query->where('type', 'cover_letter'))
                            ->searchable()
                            ->preload(),
                        Select::make('bible_study_template_id')
                            ->label('Bible Study Template')
                            ->relationship('bibleStudyTemplate', 'name', fn ($query) => $query->where('type', 'bible_study'))
                            ->searchable()
                            ->preload(),
                        Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('POD Settings')
                    ->schema([
                        TextInput::make('provider')
                            ->default('lob')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('provider_template_id')
                            ->label('Provider Template ID')
                            ->maxLength(255),
                        Select::make('mail_class')
                            ->options([
                                'marketing' => 'Marketing',
                                'first_class' => 'First Class',
                            ])
                            ->default('marketing')
                            ->required(),
                        Select::make('address_placement')
                            ->options([
                                'top_first_page' => 'Top First Page',
                                'insert_blank_page' => 'Insert Blank Page',
                            ])
                            ->default('top_first_page')
                            ->required(),
                        Toggle::make('color')
                            ->inline(false),
                        Toggle::make('double_sided')
                            ->default(true)
                            ->inline(false),
                        Toggle::make('return_envelope')
                            ->default(true)
                            ->inline(false),
                        TextInput::make('perforated_page')
                            ->numeric()
                            ->minValue(1),
                    ])
                    ->columns(2)
                    ->collapsible(),
                Section::make('Pages')
                    ->schema([
                        Repeater::make('pages')
                            ->relationship('pages')
                            ->schema([
                                TextInput::make('page_number')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1),
                                TextInput::make('name')
                                    ->maxLength(255),
                                TextInput::make('html_path')
                                    ->label('HTML Path')
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                                Textarea::make('html_content')
                                    ->label('HTML Content')
                                    ->rows(8)
                                    ->columnSpanFull(),
                                Select::make('paper_size')
                                    ->options([
                                        'letter' => 'Letter',
                                        'legal' => 'Legal',
                                    ])
                                    ->default('letter')
                                    ->required(),
                                Select::make('orientation')
                                    ->options([
                                        'portrait' => 'Portrait',
                                        'landscape' => 'Landscape',
                                    ])
                                    ->default('portrait')
                                    ->required(),
                                TextInput::make('expected_page_count')
                                    ->numeric()
                                    ->default(1)
                                    ->required()
                                    ->minValue(1),
                            ])
                            ->columns(2)
                            ->defaultItems(1)
                            ->addActionLabel('Add page')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('campaign.team.name')
                    ->label('Ministry Group')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('campaign.name')
                    ->label('Campaign')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sequence')
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                IconColumn::make('pause_until_reply')
                    ->boolean(),
                TextColumn::make('bibleStudyTemplate.name')
                    ->label('Bible Study'),
                TextColumn::make('pages_count')
                    ->label('Pages')
                    ->counts('pages')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('campaign')
                    ->relationship('campaign', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'active' => 'Active',
                        'paused' => 'Paused',
                        'archived' => 'Archived',
                    ]),
            ])
            ->defaultSort('sequence')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPodCampaignMailings::route('/'),
            'create' => CreatePodCampaignMailing::route('/create'),
            'edit' => EditPodCampaignMailing::route('/{record}/edit'),
        ];
    }
}
