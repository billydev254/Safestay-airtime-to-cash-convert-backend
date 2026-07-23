<?php

namespace App\Filament\Admin\Resources\ReceivingNumbers\Pages;

use App\Filament\Admin\Resources\ReceivingNumbers\ReceivingNumberResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListReceivingNumbers extends ListRecords
{
    protected static string $resource = ReceivingNumberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
