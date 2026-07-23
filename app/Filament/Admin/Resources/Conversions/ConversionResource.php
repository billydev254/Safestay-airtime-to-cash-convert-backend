<?php

namespace App\Filament\Admin\Resources\Conversions;

use App\Filament\Admin\Resources\Conversions\Pages\EditConversion;
use App\Filament\Admin\Resources\Conversions\Pages\ListConversions;
use App\Filament\Admin\Resources\Conversions\Schemas\ConversionForm;
use App\Filament\Admin\Resources\Conversions\Tables\ConversionsTable;
use App\Models\Conversion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ConversionResource extends Resource
{
    protected static ?string $model = Conversion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ConversionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ConversionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function canCreate(): bool
    {
        // Conversions are created by the app when a user submits an
        // airtime-to-cash/Bonga request, never manually by an admin.
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListConversions::route('/'),
            'edit' => EditConversion::route('/{record}/edit'),
        ];
    }
}
