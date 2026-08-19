<?php

namespace Nucleus\Providers\Filament\Resources\ProviderProfileResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Nucleus\Providers\Filament\Resources\ProviderProfileResource;

class EditProviderProfile extends EditRecord
{
    protected static string $resource = ProviderProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
