<?php

namespace Nucleus\Brain\Filament\Resources\AiModelResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Nucleus\Brain\Filament\Resources\AiModelResource\AiModelResource;

class ListAiModels extends ListRecords
{
    protected static string $resource = AiModelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
