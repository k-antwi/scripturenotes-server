<?php

namespace Nucleus\Onboarding\Services\AddressLookup\Providers;

use Illuminate\Support\Facades\Http;
use Nucleus\Onboarding\Services\AddressLookup\Contracts\AddressLookupProvider;
use Illuminate\Support\Facades\Log;

class GetAddressIoProvider implements AddressLookupProvider
{
    protected string $apiKey;
    protected string $baseUrl = 'https://api.getAddress.io';
    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function searchAddress(string $postcode): array
    {
        if (empty($this->apiKey)) {
            Log::warning('GetAddress.io API key is empty.');
            return [];
        }

        try {
            // Step 1: Autocomplete to find addresses matching the postcode
            // GetAddress.io requires api-key as a query parameter, not in the body
            $autocompleteResponse = Http::post(
                "{$this->baseUrl}/autocomplete/" . urlencode(trim($postcode)) . '?api-key=' . urlencode($this->apiKey),
                ['top' => 50]
            );

            if ($autocompleteResponse->successful()) {
                $suggestions = $autocompleteResponse->json()['suggestions'] ?? [];

                if (empty($suggestions)) {
                    return [];
                }

                $formattedAddresses = [];

                // Step 2: For each suggestion, we need to fetch the full expanded address details via the 'get' API.
                // Note: since this can be slow if there are many suggestions, we will fetch the full address only
                // for the ones that are likely valid or we simply map the suggestion data if it's sufficient.
                // The GetAddress autocomplete returns 'id' and 'address'.
                // To get the full split lines, we have to call `/get/{id}` for each, which is slow.
                // Alternatively, we can just split the comma-separated `address` string from the suggestion for now.

                foreach ($suggestions as $suggestion) {
                    $id = $suggestion['id'] ?? '';
                    $addressString = $suggestion['address'] ?? '';

                    if (!$id || !$addressString) {
                        continue;
                    }

                    // A basic parsing of the comma separated string returned by autocomplete
                    $parts = array_map('trim', explode(',', $addressString));
                    $parts = array_filter($parts); // remove empty parts

                    // The last part is usually the postcode, the second to last is city/town etc...
                    // But to be robust without making 20 HTTP requests, we'll assign the first part as Line1, 
                    // second as Line2, and third as City.

                    $houseNumberLine1 = array_shift($parts) ?? '';
                    // Try to separate house number from line 1
                    preg_match('/^(\d+[a-zA-Z]?|\d+-\d+)\s+(.*)/', $houseNumberLine1, $matches);

                    if (!empty($matches)) {
                        $houseNumber = $matches[1];
                        $addressLine1 = $matches[2];
                    } else {
                        $houseNumber = '';
                        $addressLine1 = $houseNumberLine1;
                    }

                    $formattedAddresses[] = [
                        'id' => $id,
                        'label' => $addressString,
                        'houseNumber' => $houseNumber,
                        'addressLine1' => $addressLine1,
                        'addressLine2' => array_shift($parts) ?? '',
                        'city' => count($parts) > 0 ? end($parts) : '', // assume last remaining part is city/town
                    ];
                }

                return $formattedAddresses;
            }

            Log::error('GetAddress.io API error: ' . $autocompleteResponse->body());
        } catch (\Exception $e) {
            Log::error('GetAddress.io Exception: ' . $e->getMessage());
        }

        return [];
    }
}
