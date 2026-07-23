<?php

namespace App\Filament\Admin\Resources\Bundles\Pages;

use App\Filament\Admin\Resources\Bundles\BundleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBundles extends ListRecords
{
    protected static string $resource = BundleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
