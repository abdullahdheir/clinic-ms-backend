<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use Filament\Widgets\ChartWidget;

class AppointmentsChartWidget extends ChartWidget
{
    protected ?string $heading = 'المواعيد — آخر 30 يوم';
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';
    protected ?string $maxHeight = '280px';

    protected function getData(): array
    {
        $data = Appointment::selectRaw('DATE(scheduled_at) as date, count(*) as count')
            ->where('scheduled_at', '>=', now()->subDays(30))
            ->groupBy('date')->orderBy('date')
            ->pluck('count', 'date');

        return [
            'datasets' => [[
                'label'           => 'عدد المواعيد',
                'data'            => $data->values()->toArray(),
                'backgroundColor' => 'rgba(11,110,110,0.08)',
                'borderColor'     => '#0B6E6E',
                'borderWidth'     => 2.5,
                'borderRadius'    => 8,
                'fill'            => true,
                'tension'         => 0.4,
                'pointBackgroundColor' => '#0B6E6E',
                'pointBorderColor'     => '#fff',
                'pointBorderWidth'     => 2,
                'pointRadius'          => 0,
                'pointHoverRadius'     => 5,
            ]],
            'labels' => $data->keys()->map(function ($date) {
                return \Carbon\Carbon::parse($date)->format('d/m');
            })->toArray(),
        ];
    }
    
    protected function getType(): string 
    { 
        return 'bar'; 
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['display' => false],
                'tooltip' => [
                    'backgroundColor' => '#1A2B3A',
                    'titleFont'       => ['family' => 'Tajawal'],
                    'bodyFont'        => ['family' => 'DM Sans'],
                    'cornerRadius'    => 10,
                    'padding'         => 12,
                    'rtl'             => true,
                    'textDirection'   => 'rtl',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'grid'        => ['color' => 'rgba(0,0,0,0.04)', 'drawBorder' => false],
                    'ticks'       => ['font' => ['family' => 'DM Sans', 'size' => 11], 'color' => '#8B97A7'],
                    'border'      => ['display' => false],
                ],
                'x' => [
                    'grid'   => ['display' => false],
                    'ticks'  => ['font' => ['family' => 'DM Sans', 'size' => 10], 'color' => '#8B97A7', 'maxRotation' => 0],
                    'border' => ['display' => false],
                ],
            ],
            'interaction' => [
                'intersect' => false,
                'mode'      => 'index',
            ],
        ];
    }
}
