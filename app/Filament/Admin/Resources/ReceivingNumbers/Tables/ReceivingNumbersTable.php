<?php

namespace App\Filament\Admin\Resources\ReceivingNumbers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ReceivingNumbersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('network')
                    ->badge()
                    ->sortable(),
                TextColumn::make('msisdn')
                    ->label('Phone number')
                    ->searchable(),
                TextColumn::make('daily_limit')
                    ->placeholder('No limit set'),
                TextColumn::make('notes')
                    ->limit(30)
                    ->toggleable(),
                IconColumn::make('active')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('network')
                    ->options([
                        'safaricom' => 'Safaricom',
                        'airtel' => 'Airtel',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
