<?php

namespace App\Filament\Resources\PodPrintLayoutTemplates\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PodPrintLayoutTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Print Layout')
                    ->description('Wraps the full rendered content stream before the HTML is sent to Lob.')
                    ->schema([
                        Select::make('scope')
                            ->options([
                                'system' => 'System',
                                'team' => 'Ministry Group',
                            ])
                            ->default('system')
                            ->required(),
                        Select::make('team_id')
                            ->label('Ministry Group')
                            ->relationship('team', 'name')
                            ->searchable()
                            ->preload(),
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->maxLength(255),
                        Select::make('mailing_format')
                            ->options([
                                'letter' => 'Letter',
                                'postcard' => 'Postcard',
                            ])
                            ->default('letter')
                            ->required(),
                        Select::make('slot')
                            ->options([
                                'letter_file' => 'Letter File',
                                'postcard_front' => 'Postcard Front',
                                'postcard_back' => 'Postcard Back',
                            ])
                            ->default('letter_file')
                            ->required(),
                        Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'active' => 'Active',
                                'archived' => 'Archived',
                            ])
                            ->default('active')
                            ->required(),
                        Textarea::make('css')
                            ->label('CSS')
                            ->rows(12)
                            ->columnSpanFull(),
                        Textarea::make('html_shell')
                            ->label('HTML Shell')
                            ->helperText('Must include {{ content }}. Use {{ css }} where the CSS field should be inserted.')
                            ->rows(16)
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
