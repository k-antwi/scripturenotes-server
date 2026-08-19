<?php

declare(strict_types=1);

namespace App\View;

use Illuminate\View\FileViewFinder;

class ThemeFileViewFinder extends FileViewFinder
{
    public function find($name): string
    {
        if (isset($this->views[$name])) {
            return $this->views[$name];
        }

        if (str_starts_with($name, 'volt-livewire::')) {
            $viewName = substr($name, strlen('volt-livewire::'));
            $segments = explode('.', $viewName);

            if (count($segments) >= 2) {
                $theme = $segments[0];
                $component = implode('/', array_slice($segments, 1));
                $path = resource_path("themes/{$theme}/livewire/{$component}.blade.php");

                if ($this->files->exists($path)) {
                    return $this->views[$name] = realpath($path) ?: $path;
                }
            }
        }

        return parent::find($name);
    }
}
