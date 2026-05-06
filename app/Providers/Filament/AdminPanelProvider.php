<?php

namespace App\Providers\Filament;

use Filament\Actions\Action;
use Filament\Http\Middleware\Authenticate;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('admin')
            ->login()
            ->default()
            ->profile()
            ->colors([
                'primary' => '#0B6E6E',
                'success' => '#2E9E6B',
                'warning' => '#F0A500',
                'danger'  => '#D94F4F',
                'info'    => '#3B82C4',
                'gray'    => '#9AA0AE',
            ])
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->brandLogo(fn() => view('filament.brand-logo'))
            ->brandLogoHeight('48px')
            ->brandName('كلينيك برو')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->font('Tajawal', provider: \Filament\FontProviders\GoogleFontProvider::class)
            ->darkMode(false)
            ->sidebarCollapsibleOnDesktop()
            ->globalSearch()
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->maxContentWidth('full')
            ->navigationItems([
                \Filament\Navigation\NavigationItem::make('الموقع الرئيسي')
                    ->url('/')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->openUrlInNewTab()
                    ->sort(99),
            ])
            ->userMenuItems([
                Action::make('profile')
                    ->label('الملف الشخصي')
                    ->icon('heroicon-o-user-circle')
                    ->url(fn() => route('filament.admin.auth.profile')),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
