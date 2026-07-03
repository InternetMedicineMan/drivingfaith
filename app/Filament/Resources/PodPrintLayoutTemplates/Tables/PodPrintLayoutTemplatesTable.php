<?php

namespace App\Filament\Resources\PodPrintLayoutTemplates\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PodPrintLayoutTemplatesTable
{
    public static function configure(Table $table): Table
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
                TextColumn::make('scope')
                    ->badge()
                    ->sortable(),
                TextColumn::make('mailing_format')
                    ->badge()
                    ->sortable(),
                TextColumn::make('slot')
                    ->badge()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
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
                SelectFilter::make('scope')
                    ->options([
                        'system' => 'System',
                        'team' => 'Ministry Group',
                    ]),
                SelectFilter::make('mailing_format')
                    ->options([
                        'letter' => 'Letter',
                        'postcard' => 'Postcard',
                    ]),
                SelectFilter::make('slot')
                    ->options([
                        'letter_file' => 'Letter File',
                        'postcard_front' => 'Postcard Front',
                        'postcard_back' => 'Postcard Back',
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
}
