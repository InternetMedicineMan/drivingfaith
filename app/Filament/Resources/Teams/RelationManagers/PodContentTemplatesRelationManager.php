<?php

namespace App\Filament\Resources\Teams\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PodContentTemplatesRelationManager extends RelationManager
{
    protected static string $relationship = 'podContentTemplates';

    protected static ?string $title = 'Cover Letter Templates';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->options([
                        'cover_letter' => 'Cover Letter',
                        'envelope_insert' => 'Envelope Insert',
                    ])
                    ->default('cover_letter')
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
                    ->label('Template HTML')
                    ->rows(12)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
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
