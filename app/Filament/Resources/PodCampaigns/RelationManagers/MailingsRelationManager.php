<?php

namespace App\Filament\Resources\PodCampaigns\RelationManagers;

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
                            ->rows(6)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->defaultItems(1)
                    ->addActionLabel('Add page')
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
                TextColumn::make('bibleStudyTemplate.name')
                    ->label('Bible Study'),
                TextColumn::make('pages_count')
                    ->label('Pages')
                    ->counts('pages')
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
