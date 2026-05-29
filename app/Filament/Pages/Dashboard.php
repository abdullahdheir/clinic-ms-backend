<?php

namespace App\Filament\Pages;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Enums\Alignment;
use Filament\Widgets\StatsOverviewWidget;
use Illuminate\Support\Facades\Auth;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'لوحة التحكم';

    public function getHeading(): string
    {
        $hour = now()->hour;
        $greeting = match (true) {
            $hour < 12  => 'صباح الخير',
            $hour < 17  => 'مساء الخير',
            default     => 'مساء الخير',
        };

        $name = Auth::user()->name ?? '';

        return $greeting . '، ' . $name . ' 👋';
    }

    public function getSubheading(): ?string
    {
        return 'إليك نظرة سريعة على أداء عياداتك اليوم — ' . now()->translatedFormat('l، j F Y');
    }

    
}
