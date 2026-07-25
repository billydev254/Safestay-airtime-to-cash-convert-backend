<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Conversion;
use Carbon\Carbon;
use Filament\Widgets\LineChartWidget;

class ConversionsChart extends LineChartWidget
{
    protected ?string $heading = 'Conversions — last 14 days';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 1;

    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $days = collect(range(13, 0))->map(fn (int $offset) => Carbon::today()->subDays($offset));

        $conversions = Conversion::where('created_at', '>=', Carbon::today()->subDays(13))
            ->get(['status', 'created_at']);

        $submitted = $days->map(
            fn (Carbon $day) => $conversions->filter(fn ($c) => $c->created_at->isSameDay($day))->count()
        );

        $paid = $days->map(
            fn (Carbon $day) => $conversions->filter(
                fn ($c) => $c->created_at->isSameDay($day) && $c->status === 'paid'
            )->count()
        );

        return [
            'datasets' => [
                [
                    'label' => 'Submitted',
                    'data' => $submitted->values(),
                    'borderColor' => '#007F3B',
                ],
                [
                    'label' => 'Paid',
                    'data' => $paid->values(),
                    'borderColor' => '#00541A',
                ],
            ],
            'labels' => $days->map(fn (Carbon $day) => $day->format('M j'))->values(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
