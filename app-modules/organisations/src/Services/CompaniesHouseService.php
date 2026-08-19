<?php

namespace Nucleus\Organisations\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Log;

class CompaniesHouseService
{
    protected string $baseUrl;
    protected ?string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.companies_house.base_url', 'https://api.company-information.service.gov.uk');
        $this->apiKey  = config('services.companies_house.api_key');
    }

    protected function client(): PendingRequest
    {
        if (empty($this->apiKey)) {
            throw new Exception('Companies House API key is not configured.');
        }

        return Http::withBasicAuth($this->apiKey, '')
            ->withHeaders(['Accept' => 'application/json'])
            ->baseUrl($this->baseUrl)
            ->timeout(10);
    }

    /**
     * Search for companies by name or number.
     *
     * @param  string  $query         Search term (company name or number)
     * @param  int     $itemsPerPage  Max results to return
     * @return array{items: array, total_results: int}
     */
    public function searchCompanies(string $query, int $itemsPerPage = 10): array
    {
        try {
            $response = $this->client()->get('/search/companies', [
                'q'               => $query,
                'items_per_page'  => $itemsPerPage,
            ]);

            if ($response->status() === 429) {
                throw new Exception('Companies House API rate limit exceeded.');
            }

            if ($response->failed()) {
                Log::warning('Companies House search failed', [
                    'query'  => $query,
                    'status' => $response->status(),
                ]);
                return ['items' => [], 'total_results' => 0];
            }

            $data = $response->json();

            return [
                'items'         => $data['items'] ?? [],
                'total_results' => $data['total_results'] ?? 0,
            ];
        } catch (Exception $e) {
            Log::error('Companies House search error', [
                'query'   => $query,
                'message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Fetch full details for a specific company by its registration number.
     *
     * @param  string  $companyNumber  Companies House registration number (e.g. 12345678)
     * @return array|null Company data, or null if not found
     */
    public function getCompany(string $companyNumber): ?array
    {
        try {
            $response = $this->client()->get("/company/{$companyNumber}");

            if ($response->status() === 404) {
                return null;
            }

            if ($response->status() === 429) {
                throw new Exception('Companies House API rate limit exceeded.');
            }

            if ($response->failed()) {
                Log::warning('Companies House company lookup failed', [
                    'company_number' => $companyNumber,
                    'status'         => $response->status(),
                ]);
                return null;
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('Companies House company lookup error', [
                'company_number' => $companyNumber,
                'message'        => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
