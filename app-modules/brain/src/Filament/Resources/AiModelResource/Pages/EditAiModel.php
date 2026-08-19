<?php

namespace Nucleus\Brain\Filament\Resources\AiModelResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Nucleus\Brain\Filament\Resources\AiModelResource\AiModelResource;

class EditAiModel extends EditRecord
{
    protected static string $resource = AiModelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
