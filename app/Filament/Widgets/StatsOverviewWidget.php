<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'نظرة عامة';

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        return [
            Stat::make('المستخدمين', User::count())
                ->description('إجمالي المستخدمين في النظام')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('الأطباء', Doctor::count())
                ->description('طبيب مسجّل في النظام')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('success'),

            Stat::make('المرضى', Patient::count())
                ->description('مريض مسجّل في النظام')
                ->descriptionIcon('heroicon-m-user')
                ->color('info'),

            Stat::make('العيادات', Clinic::count())
                ->description('عيادة مسجّلة في النظام')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('warning'),

            Stat::make('الأقسام', Department::count())
                ->description('قسم مسجّل في النظام')
                ->descriptionIcon('heroicon-m-rectangle-group')
                ->color('primary'),

            Stat::make('المواعيد', Appointment::count())
                ->description('إجمالي المواعيد')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('success'),

            Stat::make('الوصفات', Prescription::count())
                ->description('وصفة طبية مسجّلة')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),

            Stat::make('المواعيد المؤكدة', Appointment::where('status', 'confirmed')->count())
                ->description('موعد مؤكّد')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}
