<?php

namespace Nucleus\Permit\Filament\Resources\RolePermitResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Nucleus\Permit\Filament\Resources\RolePermitResource;

class EditRolePermit extends EditRecord
{
    protected static string $resource = RolePermitResource::class;

    public function getTitle(): string
    {
        return 'Manage Permissions for: ' . $this->record->name;
    }
}
