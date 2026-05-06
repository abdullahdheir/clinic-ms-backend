<?php

namespace App\Filament\Pages;

use Filament\Facades\Filament;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Auth;

class Dashboard extends BaseDashboard
{

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
