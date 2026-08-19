<?php

namespace Nucleus\Permit\Filament\Resources\PermitResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Nucleus\Permit\Filament\Resources\PermitResource;

class ListPermits extends ListRecords
{
    protected static string $resource = PermitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
