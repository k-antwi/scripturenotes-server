<?php

namespace Nucleus\Onboarding\Services\AddressLookup;

use Illuminate\Support\Manager;
use Nucleus\Onboarding\Services\AddressLookup\Providers\GetAddressIoProvider;
use Nucleus\Onboarding\Services\AddressLookup\Providers\IdealPostcodesProvider;

class AddressLookupManager extends Manager
{
    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->config->get('services.address_lookup.default', 'getaddress');
    }

    /**
     * Create an instance of the GetAddress.io driver.
     *
     * @return GetAddressIoProvider
     */
    protected function createGetaddressDriver()
    {
        return new GetAddressIoProvider(
            $this->config->get('services.address_lookup.getaddress.api_key')
        );
    }

    /**
     * Create an instance of the Ideal Postcodes driver.
     *
     * @return IdealPostcodesProvider
     */
    protected function createIdealpostcodesDriver()
    {
        return new IdealPostcodesProvider(
            $this->config->get('services.address_lookup.idealpostcodes.api_key')
        );
    }
}
