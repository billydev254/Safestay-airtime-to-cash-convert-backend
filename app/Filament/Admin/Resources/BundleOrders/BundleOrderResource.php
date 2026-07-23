<?php

namespace App\Filament\Admin\Resources\BundleOrders;

use App\Filament\Admin\Resources\BundleOrders\Pages\ListBundleOrders;
use App\Filament\Admin\Resources\BundleOrders\Schemas\BundleOrderForm;
use App\Filament\Admin\Resources\BundleOrders\Tables\BundleOrdersTable;
use App\Models\BundleOrder;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BundleOrderResource extends Resource
{
    protected static ?string $model = BundleOrder::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return BundleOrderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BundleOrdersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function canCreate(): bool
    {
        // Bundle orders are created by the app's STK push flow — delivery
        // is the client's existing phone automation, not us, so there's
        // nothing here for an admin to create or edit, only view.
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBundleOrders::route('/'),
        ];
    }
}
