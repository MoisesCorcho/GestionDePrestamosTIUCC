<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Shanerbaner82\PanelRoles\PanelRoles;

class AreaTIPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('areaTI')
            ->path('areaTI')
            ->login()
            ->profile()
            ->passwordReset()
            ->colors([
                'primary' => Color::Blue,
            ])
            ->plugins([
                PanelRoles::make()
                    ->roleToAssign('area_ti')
                    ->restrictedRoles(['area_ti', 'super_admin'])
            ])
            ->databaseNotifications()
            ->discoverResources(in: app_path('Filament/AreaTI/Resources'), for: 'App\\Filament\\AreaTI\\Resources')
            ->discoverPages(in: app_path('Filament/AreaTI/Pages'), for: 'App\\Filament\\AreaTI\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/AreaTI/Widgets'), for: 'App\\Filament\\AreaTI\\Widgets')
            ->widgets([
                // Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
