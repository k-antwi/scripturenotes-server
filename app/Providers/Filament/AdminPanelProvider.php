<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\DashboardWidget;
use Filament\Panel;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\Blade;

class AdminPanelProvider extends BasePanelProvider
{
    public function panel(Panel $panel): Panel
    {
        Blade::component('wave::admin.components.label', 'label');

        $config = config('panels.admin');
        $primary = constant(Color::class . '::' . $config['primary_color']);

        return $panel
            ->default()
            ->id('admin')
            ->path($config['path'])
            ->colors(['primary' => $primary])
            ->sidebarCollapsibleOnDesktop()
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverResources(in: base_path('app-modules/permit/src/Filament/Resources'), for: 'Nucleus\\Permit\\Filament\\Resources')
            ->discoverResources(in: base_path('app-modules/brain/src/Filament/Resources/AiModelResource'), for: 'Nucleus\\Brain\\Filament\\Resources\\AiModelResource')
            ->discoverResources(in: base_path('app-modules/kyc/src/Filament/Resources'), for: 'Nucleus\\Kyc\\Filament\\Resources')
            ->discoverResources(in: base_path('app-modules/organisations/src/Filament/Resources'), for: 'Nucleus\\Organisations\\Filament\\Resources')
            ->discoverResources(in: base_path('app-modules/providers/src/Filament/Resources'), for: 'Nucleus\\Providers\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverPages(in: base_path('app-modules/brain/src/Filament/Pages'), for: 'Nucleus\\Brain\\Filament\\Pages')
            ->pages([])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                DashboardWidget::class,
            ])
            ->middleware($this->panelMiddleware())
            ->authMiddleware($this->panelAuthMiddleware())
            ->brandLogo(fn () => view('wave::admin.logo'))
            ->darkModeBrandLogo(fn () => view('wave::admin.logo-dark'));
    }
}
