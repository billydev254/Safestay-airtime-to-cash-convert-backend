<?php

namespace App\Filament\Admin\Widgets;

use App\Models\BundleOrder;
use App\Models\Conversion;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PerformanceOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $paidOut = (int) Conversion::where('status', 'paid')->sum('amount_payout');
        $pendingConversions = Conversion::whereIn('status', ['awaiting_intake', 'paying'])->count();
        $failedConversions = Conversion::where('status', 'payout_failed')->count();

        $bundleRevenue = (int) BundleOrder::where('status', 'paid')->sum('amount');
        $pendingOrders = BundleOrder::where('status', 'pending_payment')->count();

        return [
            Stat::make('Cash Paid Out', 'KES '.number_format($paidOut))
                ->description('Airtime/Bonga conversions, all-time')
                ->color('success'),
            Stat::make('Pending Payouts', (string) $pendingConversions)
                ->description('Awaiting intake or mid-payout')
                ->color($pendingConversions > 0 ? 'warning' : 'success'),
            Stat::make('Failed Payouts', (string) $failedConversions)
                ->description('Daraja rejected or unreachable')
                ->color($failedConversions > 0 ? 'danger' : 'success'),
            Stat::make('Bundle Revenue', 'KES '.number_format($bundleRevenue))
                ->description('Data/Minutes/SMS purchases, all-time')
                ->color('success'),
            Stat::make('Pending Bundle Orders', (string) $pendingOrders)
                ->description('Waiting on STK push completion')
                ->color($pendingOrders > 0 ? 'warning' : 'success'),
        ];
    }
}
