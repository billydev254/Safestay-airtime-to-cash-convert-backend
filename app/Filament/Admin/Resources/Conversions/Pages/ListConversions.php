<?php

namespace App\Filament\Admin\Resources\Conversions\Pages;

use App\Filament\Admin\Resources\Conversions\ConversionResource;
use Filament\Resources\Pages\ListRecords;

class ListConversions extends ListRecords
{
    protected static string $resource = ConversionResource::class;

    protected function getHeaderActions(): array
    {
        // No create action — conversions are only ever created by the app.
        return [];
    }
}
