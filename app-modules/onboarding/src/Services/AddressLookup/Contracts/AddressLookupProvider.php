<?php

namespace Nucleus\Onboarding\Services\AddressLookup\Contracts;

interface AddressLookupProvider
{
    /**
     * Search for addresses using a postcode.
     *
     * @param string $postcode
     * @return array Array of formatted addresses
     */
    public function searchAddress(string $postcode): array;
}
