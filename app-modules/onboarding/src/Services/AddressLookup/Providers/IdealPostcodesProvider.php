<?php

namespace Nucleus\Onboarding\Services\AddressLookup\Providers;

use Illuminate\Support\Facades\Http;
use Nucleus\Onboarding\Services\AddressLookup\Contracts\AddressLookupProvider;
use Illuminate\Support\Facades\Log;

class IdealPostcodesProvider implements AddressLookupProvider
{
    protected string $apiKey;
    protected string $baseUrl = 'https://api.ideal-postcodes.co.uk/v1/postcodes';

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function searchAddress(string $postcode): array
    {
        if (empty($this->apiKey)) {
            Log::warning('Ideal Postcodes API key is empty.');
            return [];
        }

        try {
            $response = Http::get("{$this->baseUrl}/" . urlencode(trim($postcode)), [
                'api_key' => $this->apiKey,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $this->formatAddresses($data['result'] ?? []);
            }

            Log::error('Ideal Postcodes API error: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Ideal Postcodes Exception: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Format the raw API response into a standardized structure.
     */
    protected function formatAddresses(array $addresses): array
    {
        return array_map(function ($address) {

            $labelParts = array_filter([
                $address['line_1'] ?? '',
                $address['line_2'] ?? '',
                $address['line_3'] ?? '',
                $address['post_town'] ?? ''
            ]);

            $label = implode(', ', $labelParts);

            return [
                'id' => md5($label),
                'label' => $label,
                'houseNumber' => trim(($address['building_name'] ?? '') . ' ' . ($address['building_number'] ?? '')),
                'addressLine1' => $address['line_1'] ?? '',
                'addressLine2' => $address['line_2'] ?? '',
                'city' => $address['post_town'] ?? '',
            ];
        }, $addresses);
    }
}
