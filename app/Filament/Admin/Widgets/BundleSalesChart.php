<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Bundle;
use App\Models\BundleOrder;
use Filament\Widgets\DoughnutChartWidget;

class BundleSalesChart extends DoughnutChartWidget
{
    protected ?string $heading = 'Bundle revenue by category (paid orders)';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 1;

    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $categories = ['data', 'minutes', 'sms'];

        $revenueByCategory = collect($categories)->map(
            fn (string $category) => (int) BundleOrder::where('status', 'paid')
                ->whereHas('bundle', fn ($q) => $q->where('category', $category))
                ->sum('amount')
        );

        return [
            'datasets' => [
                [
                    'data' => $revenueByCategory->values(),
                    'backgroundColor' => ['#007F3B', '#00A651', '#D33B36'],
                ],
            ],
            'labels' => collect($categories)->map(fn ($c) => ucfirst($c))->values(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
