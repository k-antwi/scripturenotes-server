<?php

declare(strict_types=1);

namespace App\Providers;

use App\View\ThemeFileViewFinder;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Livewire\Volt\ComponentFactory;
use Livewire\Volt\Volt;

class VoltServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->registerThemeViewFinder();
        $this->registerThemeVoltComponents();
        $this->registerThemeBladeComponents();
        $this->mountVoltPaths();
    }

    protected function registerThemeViewFinder(): void
    {
        $existingFinder = $this->app['view']->getFinder();

        $finder = new ThemeFileViewFinder(
            $this->app['files'],
            $existingFinder->getPaths(),
            $existingFinder->getExtensions()
        );

        foreach ($existingFinder->getHints() as $namespace => $paths) {
            $finder->addNamespace($namespace, $paths);
        }

        $this->app['view']->setFinder($finder);
        $this->app->instance('view.finder', $finder);
    }

    protected function registerThemeVoltComponents(): void
    {
        Livewire::resolveMissingComponent(function (string $name) {
            $segments = explode('.', $name);

            if (count($segments) >= 2) {
                $theme = $segments[0];
                $component = implode('/', array_slice($segments, 1));
                $file = resource_path("themes/{$theme}/livewire/{$component}.blade.php");

                if (File::exists($file)) {
                    return $this->app->make(ComponentFactory::class)->make($name, realpath($file) ?: $file);
                }
            }

        });
    }

    protected function registerThemeBladeComponents(): void
    {
        $themesDir = resource_path('themes');
        if (File::isDirectory($themesDir)) {
            if (File::isDirectory($themesDir.'/anchor/components')) {
                Blade::anonymousComponentPath($themesDir.'/anchor/components/elements');
                Blade::anonymousComponentPath($themesDir.'/anchor/components');
            }

            foreach (File::directories($themesDir) as $themeDir) {
                if (basename($themeDir) === 'anchor') {
                    continue;
                }
                if (File::isDirectory($themeDir.'/components/elements')) {
                    Blade::anonymousComponentPath($themeDir.'/components/elements');
                }
                if (File::isDirectory($themeDir.'/components')) {
                    Blade::anonymousComponentPath($themeDir.'/components');
                }
            }
        }
    }

    protected function mountVoltPaths(): void
    {
        $paths = [
            config('livewire.view_path', resource_path('views/livewire')),
            resource_path('views/pages'),
        ];

        $themesDir = resource_path('themes');
        if (File::isDirectory($themesDir)) {
            foreach (File::directories($themesDir) as $themeDir) {
                $pagesDir = $themeDir.'/pages';
                if (File::isDirectory($pagesDir)) {
                    $paths[] = $pagesDir;
                }
            }
        }

        Volt::mount($paths);
    }
}
