<?php

namespace App\Filament\Admin\Resources\ReceivingNumbers\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ReceivingNumberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('network')
                    ->options([
                        'safaricom' => 'Safaricom',
                        'airtel' => 'Airtel',
                    ])
                    ->required(),
                TextInput::make('msisdn')
                    ->label('Phone number')
                    ->tel()
                    ->required(),
                TextInput::make('daily_limit')
                    ->numeric()
                    ->helperText('Leave blank for no set limit.'),
                TextInput::make('notes')
                    ->helperText('e.g. which physical SIM/device this is.'),
                Toggle::make('active')
                    ->default(true)
                    ->helperText('Only active numbers are shown to the app as receiving options.'),
            ]);
    }
}
