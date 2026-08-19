<?php

namespace Nucleus\Providers\Filament\Resources\ProviderProfileResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Nucleus\Providers\Filament\Resources\ProviderProfileResource;

class ListProviderProfiles extends ListRecords
{
    protected static string $resource = ProviderProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
