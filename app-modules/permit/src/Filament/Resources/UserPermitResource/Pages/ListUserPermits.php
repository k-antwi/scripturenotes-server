<?php

namespace Nucleus\Permit\Filament\Resources\UserPermitResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Nucleus\Permit\Filament\Resources\UserPermitResource;

class ListUserPermits extends ListRecords
{
    protected static string $resource = UserPermitResource::class;
}
