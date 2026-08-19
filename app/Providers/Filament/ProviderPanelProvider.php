<?php

namespace App\Providers\Filament;

use Filament\Panel;
use Filament\Support\Colors\Color;

class ProviderPanelProvider extends BasePanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $config = config('panels.provider');
        $primary = constant(Color::class . '::' . $config['primary_color']);

        return $panel
            ->id('provider')
            ->path($config['path'])
            ->colors(['primary' => $primary])
            ->sidebarCollapsibleOnDesktop()
            ->discoverResources(in: base_path('app-modules/providers/src/Filament/Resources'), for: 'Nucleus\\Providers\\Filament\\Resources')
            ->discoverPages(in: base_path('app-modules/providers/src/Filament/Pages'), for: 'Nucleus\\Providers\\Filament\\Pages')
            ->pages([])
            ->discoverWidgets(in: base_path('app-modules/providers/src/Filament/Widgets'), for: 'Nucleus\\Providers\\Filament\\Widgets')
            ->middleware($this->panelMiddleware())
            ->authMiddleware($this->panelAuthMiddleware());
    }
}
