<?php

namespace App\Filament\Resources\PodPrintLayoutTemplates;

use App\Filament\Resources\PodPrintLayoutTemplates\Pages\CreatePodPrintLayoutTemplate;
use App\Filament\Resources\PodPrintLayoutTemplates\Pages\EditPodPrintLayoutTemplate;
use App\Filament\Resources\PodPrintLayoutTemplates\Pages\ListPodPrintLayoutTemplates;
use App\Filament\Resources\PodPrintLayoutTemplates\Schemas\PodPrintLayoutTemplateForm;
use App\Filament\Resources\PodPrintLayoutTemplates\Tables\PodPrintLayoutTemplatesTable;
use App\Models\PodPrintLayoutTemplate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PodPrintLayoutTemplateResource extends Resource
{
    protected static ?string $model = PodPrintLayoutTemplate::class;

    protected static ?string $navigationLabel = 'Print Layout Templates';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|\UnitEnum|null $navigationGroup = 'Ministry Modules';

    protected static ?string $navigationParentItem = 'Bible Study Campaigns';

    protected static ?int $navigationSort = 13;

    public static function form(Schema $schema): Schema
    {
        return PodPrintLayoutTemplateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PodPrintLayoutTemplatesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPodPrintLayoutTemplates::route('/'),
            'create' => CreatePodPrintLayoutTemplate::route('/create'),
            'edit' => EditPodPrintLayoutTemplate::route('/{record}/edit'),
        ];
    }
}
