<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use Filament\Widgets\ChartWidget;

class StatusDonutWidget extends ChartWidget
{
    protected ?string $heading = 'توزيع حالات المواعيد';
    protected static ?int $sort = 3;
    protected ?string $maxHeight = '280px';

    protected function getData(): array
    {
        $statuses = Appointment::selectRaw('status, count(*) as count')
            ->groupBy('status')->pluck('count', 'status');

        $labels = $statuses->keys()->map(fn($s) => match ($s) {
            'confirmed'   => 'مؤكّد',
            'pending'     => 'انتظار',
            'cancelled'   => 'ملغي',
            'done'        => 'منتهي',
            'in_progress' => 'جارٍ',
            'no_show'     => 'لم يحضر',
            default       => $s,
        })->toArray();

        return [
            'datasets' => [[
                'data'            => $statuses->values()->toArray(),
                'backgroundColor' => [
                    '#0B6E6E',  // confirmed - primary teal
                    '#F0A500',  // pending   - amber
                    '#D94F4F',  // cancelled - red
                    '#2E9E6B',  // done      - green
                    '#3B82C4',  // in_progress - blue
                    '#8B97A7',  // no_show   - gray
                ],
                'borderWidth'     => 0,
                'hoverOffset'     => 8,
                'borderRadius'    => 4,
                'spacing'         => 2,
            ]],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'cutout'  => '72%',
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                    'rtl'      => true,
                    'labels'   => [
                        'padding'       => 16,
                        'usePointStyle' => true,
                        'pointStyle'    => 'rectRounded',
                        'font'          => ['family' => 'Tajawal', 'size' => 12],
                        'color'         => '#6B7A8D',
                    ],
                ],
                'tooltip' => [
                    'backgroundColor' => '#1A2B3A',
                    'cornerRadius'    => 10,
                    'padding'         => 12,
                    'rtl'             => true,
                    'textDirection'   => 'rtl',
                    'titleFont'       => ['family' => 'Tajawal'],
                    'bodyFont'        => ['family' => 'DM Sans'],
                ],
            ],
        ];
    }
}
