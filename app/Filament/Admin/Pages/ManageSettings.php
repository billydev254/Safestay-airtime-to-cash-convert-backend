<?php

namespace App\Filament\Admin\Pages;

use App\Models\Setting;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ManageSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.admin.pages.manage-settings';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'safaricom_cashback_pct' => Setting::get('safaricom_cashback_pct', 80),
            'airtel_cashback_pct' => Setting::get('airtel_cashback_pct', 50),
            'bonga_rate' => Setting::get('bonga_rate', 50),
            'till_shortcode' => Setting::get('till_shortcode'),
            'paybill_shortcode' => Setting::get('paybill_shortcode'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('safaricom_cashback_pct')
                    ->label('Safaricom cashback %')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->suffix('%')
                    ->required(),
                TextInput::make('airtel_cashback_pct')
                    ->label('Airtel cashback %')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->suffix('%')
                    ->required(),
                TextInput::make('bonga_rate')
                    ->label('Bonga points cashback %')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->suffix('%')
                    ->required(),
                TextInput::make('till_shortcode')
                    ->label('Business Till (bundle purchases)')
                    ->helperText('Buy Goods till — used for STK push / C2B on bundle purchases.'),
                TextInput::make('paybill_shortcode')
                    ->label('Paybill (airtime-to-cash payouts)')
                    ->helperText('B2C payouts require a Paybill/Org shortcode, not a Till.'),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            Setting::set($key, (string) $value);
        }

        Notification::make()
            ->title('Settings saved')
            ->success()
            ->send();
    }
}
