<?php

namespace App\Filament\Admin\Resources\Bundles\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BundleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category')
                    ->options([
                        'data' => 'Data',
                        'minutes' => 'Minutes',
                        'sms' => 'SMS',
                    ])
                    ->required(),
                TextInput::make('label')
                    ->required(),
                TextInput::make('price')
                    ->label('Price (KES)')
                    ->numeric()
                    ->required(),
                TextInput::make('validity_text')
                    ->label('Validity')
                    ->placeholder('e.g. Valid 24 Hours')
                    ->required(),
                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0)
                    ->helperText('Lower numbers show first within a category.'),
                Toggle::make('active')
                    ->default(true)
                    ->helperText('Inactive bundles are hidden from the app immediately.'),
            ]);
    }
}
