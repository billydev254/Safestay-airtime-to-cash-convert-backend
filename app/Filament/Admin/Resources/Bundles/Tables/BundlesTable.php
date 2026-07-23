<?php

namespace App\Filament\Admin\Resources\Bundles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BundlesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('category')
                    ->badge()
                    ->sortable(),
                TextColumn::make('label')
                    ->searchable(),
                TextColumn::make('price')
                    ->label('Price (KES)')
                    ->sortable(),
                TextColumn::make('validity_text')
                    ->label('Validity'),
                IconColumn::make('active')
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('category')
            ->filters([
                SelectFilter::make('category')
                    ->options([
                        'data' => 'Data',
                        'minutes' => 'Minutes',
                        'sms' => 'SMS',
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
