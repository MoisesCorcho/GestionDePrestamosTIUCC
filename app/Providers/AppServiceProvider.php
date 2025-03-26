<?php

namespace App\Providers;

use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use BezhanSalleh\PanelSwitch\PanelSwitch;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::unguard();

        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
                ->locales(['es', 'en']) // also accepts a closure
                ->circular();
        });

        PanelSwitch::configureUsing(function (PanelSwitch $panelSwitch) {
            $panelSwitch
                ->visible(fn(): bool => auth()->user()?->hasAnyRole([
                    'super_admin',
                    'area_ti',
                ]))
                ->panels(function () {

                    $hasRoleSuperAdmin = auth()->user()?->hasAnyRole([
                        'super_admin',
                    ]);

                    $hasRoleAreaTI = auth()->user()?->hasAnyRole([
                        'area_ti',
                    ]);

                    // SuperAdmin User can see all panels
                    if ($hasRoleSuperAdmin) {
                        return ['admin', 'areaTI', 'personal'];
                    }

                    // Area TI Users can´t see admin panel
                    if ($hasRoleAreaTI) {
                        return ['areaTI', 'personal'];
                    }
                })
                ->modalHeading('Available Panels')
                ->modalWidth('sm')
                ->slideOver()
                ->icons([
                    'admin' => 'heroicon-o-cog',
                    'areaTI' => 'heroicon-o-server',
                    'personal' => 'heroicon-o-user-group',
                ])
                ->iconSize(16)
                ->labels([
                    'admin' => 'Admin Panel',
                    'areaTI' => 'TI Area Panel',
                    'personal' => 'User Panel',
                ]);
        });

        // Set default table pagination
        Table::configureUsing(function (Table $table) {
            $table->paginated([10, 25, 50]);
        });
    }
}
