<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use Laravel\Folio\Folio;

class FolioServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $anchorPages = resource_path('themes/anchor/pages');

        if (File::isDirectory($anchorPages)) {
            Folio::path($anchorPages)->middleware([
                '*' => [
                    //
                ],
            ]);
        }
    }
}
