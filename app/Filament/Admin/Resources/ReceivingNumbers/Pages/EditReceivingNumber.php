<?php

namespace App\Filament\Admin\Resources\ReceivingNumbers\Pages;

use App\Filament\Admin\Resources\ReceivingNumbers\ReceivingNumberResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditReceivingNumber extends EditRecord
{
    protected static string $resource = ReceivingNumberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
