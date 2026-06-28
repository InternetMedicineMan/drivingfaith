<?php

namespace App\Filament\Resources\PodCampaignEnrollments\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EnrollmentMailingsRelationManager extends RelationManager
{
    protected static string $relationship = 'enrollmentMailings';

    protected static ?string $title = 'Planned Mailings';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('status')
                    ->options([
                        'planned' => 'Planned',
                        'sent' => 'Sent',
                        'failed' => 'Failed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->required(),
                DatePicker::make('scheduled_for'),
                Select::make('cover_letter_template_id')
                    ->label('Default Cover Letter Template')
                    ->relationship('coverLetterTemplate', 'name', fn ($query) => $query->where('type', 'cover_letter'))
                    ->searchable()
                    ->preload(),
                Select::make('override_cover_letter_template_id')
                    ->label('Override Cover Letter Template')
                    ->relationship('overrideCoverLetterTemplate', 'name', fn ($query) => $query->where('type', 'cover_letter'))
                    ->searchable()
                    ->preload(),
                Textarea::make('override_cover_letter_html')
                    ->label('One-off Cover Letter HTML')
                    ->rows(12)
                    ->columnSpanFull(),
                Textarea::make('cover_letter_override_reason')
                    ->label('Override Reason')
                    ->rows(3)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('sequence')
            ->columns([
                TextColumn::make('sequence')
                    ->sortable(),
                TextColumn::make('campaignMailing.name')
                    ->label('Mailing Step')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('scheduled_for')
                    ->date()
                    ->sortable(),
                TextColumn::make('coverLetterTemplate.name')
                    ->label('Default Cover Letter'),
                TextColumn::make('overrideCoverLetterTemplate.name')
                    ->label('Override Template'),
                TextColumn::make('cover_letter_override_reason')
                    ->label('Override Reason')
                    ->limit(50),
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
}
