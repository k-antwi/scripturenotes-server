<?php

namespace Nucleus\Permit\Filament\Resources\UserPermitResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Nucleus\Permit\Filament\Resources\UserPermitResource;

class EditUserPermit extends EditRecord
{
    protected static string $resource = UserPermitResource::class;

    public function getTitle(): string
    {
        return 'Manage Permissions for: ' . $this->record->name;
    }
}
