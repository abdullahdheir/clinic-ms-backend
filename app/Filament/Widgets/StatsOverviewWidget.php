<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\User;
use App\Models\Visit;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'نظرة عامة';

    protected function getColumns(): int
    {
        return 3;
    }

    protected function getStats(): array
    {
        $todayAppts = Appointment::whereDate('scheduled_at', today())->count();
        $confirmedToday = Appointment::whereDate('scheduled_at', today())
            ->where('status', 'confirmed')->count();

        $last7Days = Appointment::selectRaw('DATE(scheduled_at) as date, count(*) as count')
            ->where('scheduled_at', '>=', now()->subDays(7))
            ->groupBy('date')->orderBy('date')
            ->pluck('count')->toArray();

        $thisMonth = Appointment::whereMonth('scheduled_at', now()->month)->count();
        $lastMonth = Appointment::whereMonth('scheduled_at', now()->subMonth()->month)->count();
        $monthDiff = $lastMonth > 0 
            ? round((($thisMonth - $lastMonth) / $lastMonth) * 100, 1) 
            : 0;
        $monthTrend = $monthDiff >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
        $monthColor = $monthDiff >= 0 ? 'success' : 'danger';

        return [
            Stat::make('إجمالي العيادات', Clinic::count())
                ->description('عيادات مسجّلة في النظام')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('primary'),

            Stat::make('مواعيد اليوم', $todayAppts)
                ->description($confirmedToday . ' مؤكّد من أصل ' . $todayAppts)
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('success')
                ->chart($last7Days),

            Stat::make('إجمالي المرضى', User::role('patient')->count())
                ->description('مريض مسجّل في النظام')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),

            Stat::make('الأطباء النشطون', Doctor::count())
                ->description('في جميع العيادات')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('warning'),

            Stat::make('مواعيد هذا الشهر', $thisMonth)
                ->description(abs($monthDiff) . '% ' . ($monthDiff >= 0 ? 'زيادة' : 'انخفاض') . ' عن الشهر الماضي')
                ->descriptionIcon($monthTrend)
                ->color($monthColor),

            Stat::make('إجمالي الزيارات', Visit::count())
                ->description('زيارة طبية مسجّلة')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),
        ];
    }
}
