<?php

namespace App\Filament\Admin\Resources\BundleOrders\Pages;

use App\Filament\Admin\Resources\BundleOrders\BundleOrderResource;
use Filament\Resources\Pages\ListRecords;

class ListBundleOrders extends ListRecords
{
    protected static string $resource = BundleOrderResource::class;

    protected function getHeaderActions(): array
    {
        // No create action — bundle orders are only ever created by the
        // app's STK push flow.
        return [];
    }
}
