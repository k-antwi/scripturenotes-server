<?php

namespace Nucleus\Organisations\Livewire;

use Exception;
use Livewire\Component;
use Nucleus\Organisations\Services\CompaniesHouseService;

class CompanyHouseSearch extends Component
{
    public string $searchQuery = '';
    public array $searchResults = [];
    public bool $isSearching = false;
    public string $searchError = '';
    public bool $hasSearched = false;

    public function search(): void
    {
        $this->validate([
            'searchQuery' => 'required|string|min:2|max:160',
        ]);

        $this->isSearching  = true;
        $this->searchError  = '';
        $this->searchResults = [];

        try {
            /** @var CompaniesHouseService $service */
            $service = app(CompaniesHouseService::class);

            $result = $service->searchCompanies($this->searchQuery);

            $this->searchResults = $result['items'];
        } catch (Exception $e) {
            $this->searchError = 'Unable to search Companies House at this time. Please try again or enter details manually.';
        } finally {
            $this->isSearching = false;
            $this->hasSearched = true;
        }
    }

    public function selectCompany(string $companyNumber): void
    {
        $this->searchError = '';

        try {
            /** @var CompaniesHouseService $service */
            $service = app(CompaniesHouseService::class);

            $company = $service->getCompany($companyNumber);

            if (!$company) {
                $this->searchError = 'Company details could not be retrieved.';
                return;
            }

            $address = $company['registered_office_address'] ?? [];

            $this->dispatch('company-selected', [
                'name'               => $company['company_name'] ?? '',
                'registrationNumber' => $company['company_number'] ?? '',
                'status'             => $this->mapCompanyStatus($company['company_status'] ?? ''),
                'industryCode'       => $company['sic_codes'][0] ?? '',
                'addressLine1'       => $address['address_line_1'] ?? '',
                'addressLine2'       => $address['address_line_2'] ?? '',
                'city'               => $address['locality'] ?? '',
                'county'             => $address['region'] ?? '',
                'postcode'           => $address['postal_code'] ?? '',
                'countryCode'        => 'GB',
            ]);

            // Clear results after selection
            $this->searchResults = [];
            $this->searchQuery   = '';
            $this->hasSearched   = false;
        } catch (Exception $e) {
            $this->searchError = 'Unable to retrieve company details. Please try again or enter details manually.';
        }
    }

    protected function mapCompanyStatus(string $companiesHouseStatus): string
    {
        return match (strtolower($companiesHouseStatus)) {
            'active'             => 'active',
            'dissolved'          => 'dissolved',
            'liquidation',
            'administration',
            'voluntary-arrangement',
            'converted-closed'   => 'suspended',
            default              => 'active',
        };
    }

    public function clearSearch(): void
    {
        $this->searchQuery   = '';
        $this->searchResults = [];
        $this->searchError   = '';
        $this->hasSearched   = false;
    }

    public function render()
    {
        return view('organisations::livewire.company-house-search');
    }
}
