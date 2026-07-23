<?php

namespace App\Filament\Admin\Resources\ReceivingNumbers;

use App\Filament\Admin\Resources\ReceivingNumbers\Pages\CreateReceivingNumber;
use App\Filament\Admin\Resources\ReceivingNumbers\Pages\EditReceivingNumber;
use App\Filament\Admin\Resources\ReceivingNumbers\Pages\ListReceivingNumbers;
use App\Filament\Admin\Resources\ReceivingNumbers\Schemas\ReceivingNumberForm;
use App\Filament\Admin\Resources\ReceivingNumbers\Tables\ReceivingNumbersTable;
use App\Models\ReceivingNumber;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ReceivingNumberResource extends Resource
{
    protected static ?string $model = ReceivingNumber::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ReceivingNumberForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReceivingNumbersTable::configure($table);
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
            'index' => ListReceivingNumbers::route('/'),
            'create' => CreateReceivingNumber::route('/create'),
            'edit' => EditReceivingNumber::route('/{record}/edit'),
        ];
    }
}
