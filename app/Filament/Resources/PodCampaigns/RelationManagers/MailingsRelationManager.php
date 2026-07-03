<?php

namespace App\Filament\Resources\PodCampaigns\RelationManagers;

use App\Models\PodCampaignMailing;
use App\Models\PodPrintLayoutTemplate;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Validation\Rule;

class MailingsRelationManager extends RelationManager
{
    protected static string $relationship = 'mailings';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('sequence')
                    ->numeric()
                    ->required()
                    ->default(fn (RelationManager $livewire): int => static::nextSequenceForCampaign((int) $livewire->getOwnerRecord()->getKey()))
                    ->minValue(1)
                    ->rules(fn (RelationManager $livewire, $record): array => [
                        tap(Rule::unique('pod_campaign_mailings', 'sequence')
                            ->where('campaign_id', $livewire->getOwnerRecord()->getKey()), function ($rule) use ($record): void {
                                if ($record?->exists) {
                                    $rule->ignore($record->getKey());
                                }
                            }),
                    ])
                    ->validationMessages([
                        'unique' => 'This campaign already has a mailing with that sequence number. Use the next step number instead.',
                    ]),
                TextInput::make('delay_days_after_previous')
                    ->label('Days After Previous Mailing')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->helperText('For sequence 1, use 0. For later steps, this is the wait after the previous mailing is sent before this step is scheduled.'),
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
                Select::make('print_layout_template_id')
                    ->label('Print Layout')
                    ->options(fn (RelationManager $livewire): array => static::printLayoutOptions($livewire->getOwnerRecord()?->team_id))
                    ->searchable()
                    ->preload()
                    ->helperText('Wraps the complete cover letter and lesson content before Lob fetches the render URL.'),
                Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull(),
                Repeater::make('pages')
                    ->relationship('pages')
                    ->schema([
                        TextInput::make('page_number')
                            ->numeric()
                            ->required()
                            ->minValue(1),
                        TextInput::make('name')
                            ->label('Page Name')
                            ->maxLength(255),
                        TextInput::make('html_path')
                            ->label('HTML Path')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Textarea::make('html_content')
                            ->label('Full Page HTML')
                            ->rows(10)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->defaultItems(1)
                    ->addActionLabel('Add Bible study page')
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
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
                TextColumn::make('pages_count')
                    ->label('Bible Study Pages')
                    ->counts('pages')
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->createAnother(false),
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

    private static function nextSequenceForCampaign(int $campaignId): int
    {
        return ((int) PodCampaignMailing::query()
            ->where('campaign_id', $campaignId)
            ->max('sequence')) + 1;
    }

    /**
     * @return array<int, string>
     */
    private static function printLayoutOptions(?int $teamId): array
    {
        return PodPrintLayoutTemplate::query()
            ->where('mailing_format', 'letter')
            ->where('slot', 'letter_file')
            ->where('status', 'active')
            ->where(function ($query) use ($teamId): void {
                $query->where('scope', 'system')
                    ->orWhere(function ($query) use ($teamId): void {
                        $query->where('scope', 'team');

                        $teamId
                            ? $query->where('team_id', $teamId)
                            : $query->whereRaw('1 = 0');
                    });
            })
            ->orderByRaw("CASE WHEN scope = 'team' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }
}
