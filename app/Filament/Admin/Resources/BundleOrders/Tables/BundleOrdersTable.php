<?php

namespace App\Filament\Admin\Resources\BundleOrders\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BundleOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('bundle.label')->label('Bundle'),
                TextColumn::make('recipient_number')->label('Recipient'),
                TextColumn::make('mpesa_number')->label('Paid From'),
                TextColumn::make('amount')->label('Amount (KES)'),
                TextColumn::make('status')->badge(),
                TextColumn::make('mpesa_receipt')->placeholder('—'),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending_payment' => 'Pending Payment',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                    ]),
            ]);
    }
}
