<?php

namespace App\Filament\Components;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class BrandLogo extends Component
{
    public function render(): View
    {
        return view('filament.brand-logo');
    }
}
