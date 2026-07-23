<?php

namespace App\Filament\Admin\Resources\Conversions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ConversionForm
{
    public static function configure(Schema $schema): Schema
    {
        // Read-only view of a conversion — the record itself is created by
        // the app and progressed via the "Mark airtime received" table
        // action, not by editing fields here.
        return $schema
            ->components([
                TextInput::make('type')->disabled(),
                Select::make('network')
                    ->options(['safaricom' => 'Safaricom', 'airtel' => 'Airtel'])
                    ->disabled(),
                TextInput::make('sender_number')->disabled(),
                TextInput::make('mpesa_number')->disabled(),
                TextInput::make('amount_in')->label('Amount In')->disabled(),
                TextInput::make('cashback_pct')->label('Cashback %')->disabled(),
                TextInput::make('amount_payout')->label('Payout Amount')->disabled(),
                TextInput::make('status')->disabled(),
                TextInput::make('mpesa_receipt')->disabled(),
                TextInput::make('payout_result_desc')->label('Payout Result')->disabled(),
            ]);
    }
}
