<?php

namespace Nucleus\Organisations\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Nucleus\Organisations\Livewire\CompanyHouseSearch;

class OrganisationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'organisations');

        Livewire::component('organisations.company-house-search', CompanyHouseSearch::class);
    }
}
