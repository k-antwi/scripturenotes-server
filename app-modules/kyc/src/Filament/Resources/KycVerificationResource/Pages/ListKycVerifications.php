<?php

namespace Nucleus\Kyc\Filament\Resources\KycVerificationResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Nucleus\Kyc\Filament\Resources\KycVerificationResource;

class ListKycVerifications extends ListRecords
{
    protected static string $resource = KycVerificationResource::class;
}
