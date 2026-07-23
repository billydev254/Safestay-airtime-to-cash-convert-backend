<?php

namespace App\Filament\Admin\Resources\Conversions\Tables;

use App\Models\Conversion;
use App\Services\AirtimeIntake\IntakeInterface;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ConversionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')->badge(),
                TextColumn::make('network')->placeholder('—'),
                TextColumn::make('sender_number')->label('Sender'),
                TextColumn::make('mpesa_number')->label('M-Pesa Number'),
                TextColumn::make('amount_in')->label('Amount In'),
                TextColumn::make('amount_payout')->label('Payout'),
                TextColumn::make('status')->badge(),
                TextColumn::make('mpesa_receipt')->placeholder('—')->toggleable(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'awaiting_intake' => 'Awaiting Intake',
                        'paying' => 'Paying',
                        'paid' => 'Paid',
                        'payout_failed' => 'Payout Failed',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->recordActions([
                Action::make('markReceived')
                    ->label('Mark airtime received')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Conversion $record) => $record->status === 'awaiting_intake')
                    ->requiresConfirmation()
                    ->modalDescription('This confirms the airtime/Bonga points landed on your line and immediately triggers the M-Pesa payout. Only confirm once you have verified receipt.')
                    ->action(function (Conversion $record) {
                        app(IntakeInterface::class)->markReceived($record);

                        Notification::make()
                            ->title('Payout triggered')
                            ->success()
                            ->send();
                    }),
                EditAction::make()
                    ->label('View'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
