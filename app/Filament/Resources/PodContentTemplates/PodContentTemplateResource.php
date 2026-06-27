<?php

namespace App\Filament\Resources\PodContentTemplates;

use App\Filament\Resources\PodContentTemplates\Pages\CreatePodContentTemplate;
use App\Filament\Resources\PodContentTemplates\Pages\EditPodContentTemplate;
use App\Filament\Resources\PodContentTemplates\Pages\ListPodContentTemplates;
use App\Models\PodContentTemplate;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PodContentTemplateResource extends Resource
{
    protected static ?string $model = PodContentTemplate::class;

    protected static ?string $navigationLabel = 'POD Content Templates';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|\UnitEnum|null $navigationGroup = 'Ministry Modules';

    protected static ?string $navigationParentItem = 'Bible Study Campaigns';

    protected static ?int $navigationSort = 11;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Template')
                    ->schema([
                        Select::make('team_id')
                            ->label('Ministry Group')
                            ->relationship('team', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('type')
                            ->options([
                                'cover_letter' => 'Cover Letter',
                                'bible_study' => 'Bible Study',
                                'reply_card' => 'Reply Card',
                                'envelope_insert' => 'Envelope Insert',
                            ])
                            ->default('bible_study')
                            ->required(),
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->maxLength(255),
                        TextInput::make('version')
                            ->numeric()
                            ->default(1)
                            ->required(),
                        Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'active' => 'Active',
                                'archived' => 'Archived',
                            ])
                            ->default('draft')
                            ->required(),
                        TextInput::make('provider')
                            ->default('lob')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('provider_template_id')
                            ->label('Provider Template ID')
                            ->maxLength(255),
                        TextInput::make('html_path')
                            ->label('HTML Path')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Textarea::make('html_content')
                            ->label('HTML Content')
                            ->rows(14)
                            ->columnSpanFull(),
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
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('version')
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
                SelectFilter::make('type')
                    ->options([
                        'cover_letter' => 'Cover Letter',
                        'bible_study' => 'Bible Study',
                        'reply_card' => 'Reply Card',
                        'envelope_insert' => 'Envelope Insert',
                    ]),
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'active' => 'Active',
                        'archived' => 'Archived',
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

    public static function getPages(): array
    {
        return [
            'index' => ListPodContentTemplates::route('/'),
            'create' => CreatePodContentTemplate::route('/create'),
            'edit' => EditPodContentTemplate::route('/{record}/edit'),
        ];
    }
}
