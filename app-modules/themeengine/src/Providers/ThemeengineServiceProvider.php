<?php

namespace Nucleus\Themeengine\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Nucleus\Themeengine\Livewire\ThemeChat;
use Nucleus\Themeengine\Livewire\WebsiteChat;

class ThemeengineServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/themeengine.php', 'themeengine');
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'themeengine');

        Livewire::component('themeengine.theme-chat', ThemeChat::class);
        Livewire::component('themeengine.website-chat', WebsiteChat::class);

        $this->publishes([
            __DIR__ . '/../../config/themeengine.php' => config_path('themeengine.php'),
        ], 'themeengine-config');

        $this->registerThemeAgent();
        $this->registerWebsiteAgent();
    }

    /**
     * Register the theme agent with the Brain module.
     *
     * Done here rather than in config/brain.php so the Brain module stays
     * domain-agnostic. An explicit mapping in config/brain.php → agents takes
     * precedence, so forks can substitute their own agent.
     */
    private function registerThemeAgent(): void
    {
        $role  = config('themeengine.agent_role', 'theme');
        $agent = config('themeengine.agent');

        if (! $agent || config("brain.agents.$role")) {
            return;
        }

        config(["brain.agents.$role" => $agent]);
    }

    private function registerWebsiteAgent(): void
    {
        $role  = config('themeengine.website_agent_role', 'website');
        $agent = config('themeengine.website_agent');

        if (! $agent || config("brain.agents.$role")) {
            return;
        }

        config(["brain.agents.$role" => $agent]);
    }
}
