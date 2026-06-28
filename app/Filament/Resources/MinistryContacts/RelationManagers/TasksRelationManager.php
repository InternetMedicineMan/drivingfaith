<?php

namespace App\Filament\Resources\MinistryContacts\RelationManagers;

use App\Models\MinistryContactEvent;
use App\Models\MinistryContactTask;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TasksRelationManager extends RelationManager
{
    protected static string $relationship = 'tasks';

    protected static ?string $title = 'Tasks';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->options([
                        'phone_call' => 'Phone Call',
                        'follow_up' => 'Follow Up',
                        'mailing_review' => 'Mailing Review',
                        'visit' => 'Visit',
                        'other' => 'Other',
                    ])
                    ->default('follow_up')
                    ->required(),
                Select::make('status')
                    ->options([
                        'open' => 'Open',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->default('open')
                    ->required(),
                Select::make('priority')
                    ->options([
                        'low' => 'Low',
                        'normal' => 'Normal',
                        'high' => 'High',
                        'urgent' => 'Urgent',
                    ])
                    ->default('normal')
                    ->required(),
                Select::make('assigned_to_user_id')
                    ->label('Assigned To')
                    ->relationship('assignedTo', 'name')
                    ->searchable()
                    ->preload(),
                DateTimePicker::make('due_at'),
                TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Textarea::make('description')
                    ->rows(4)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('priority')
                    ->badge()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->sortable(),
                TextColumn::make('title')
                    ->searchable()
                    ->limit(60),
                TextColumn::make('assignedTo.name')
                    ->label('Assigned To')
                    ->searchable(),
                TextColumn::make('due_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('completed_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'open' => 'Open',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
                SelectFilter::make('type')
                    ->options([
                        'phone_call' => 'Phone Call',
                        'follow_up' => 'Follow Up',
                        'mailing_review' => 'Mailing Review',
                        'visit' => 'Visit',
                        'other' => 'Other',
                    ]),
            ])
            ->defaultSort('due_at')
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                Action::make('complete')
                    ->label('Complete')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn (MinistryContactTask $record): bool => $record->status !== 'completed')
                    ->action(function (MinistryContactTask $record): void {
                        $event = MinistryContactEvent::query()->create([
                            'team_id' => $record->team_id,
                            'contact_id' => $record->contact_id,
                            'user_id' => auth()->id(),
                            'eventable_type' => MinistryContactTask::class,
                            'eventable_id' => $record->id,
                            'type' => 'task_completed',
                            'source' => 'manual',
                            'source_label' => $record->type,
                            'summary' => "Completed task: {$record->title}",
                            'occurred_at' => now(),
                        ]);

                        $record->update([
                            'status' => 'completed',
                            'completed_at' => now(),
                            'completed_by_user_id' => auth()->id(),
                            'completion_event_id' => $event->id,
                        ]);
                    }),
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
