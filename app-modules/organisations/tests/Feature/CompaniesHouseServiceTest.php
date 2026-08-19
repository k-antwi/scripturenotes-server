<?php

use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Nucleus\Organisations\Services\CompaniesHouseService;

beforeEach(function () {
    config([
        'services.companies_house.api_key'  => 'test-api-key',
        'services.companies_house.base_url' => 'https://api.company-information.service.gov.uk',
    ]);
});

// ── searchCompanies ────────────────────────────────────────────────────────────

describe('CompaniesHouseService::searchCompanies', function () {

    it('returns items and total_results on a successful search', function () {
        Http::fake([
            'api.company-information.service.gov.uk/search/companies*' => Http::response([
                'total_results' => 2,
                'items' => [
                    [
                        'company_number' => '12345678',
                        'title'          => 'ACME LTD',
                        'company_status' => 'active',
                        'address'        => ['locality' => 'London', 'postal_code' => 'SW1A 1AA'],
                    ],
                    [
                        'company_number' => '87654321',
                        'title'          => 'ACME HOLDINGS LTD',
                        'company_status' => 'active',
                        'address'        => ['locality' => 'Manchester', 'postal_code' => 'M1 1AA'],
                    ],
                ],
            ], 200),
        ]);

        $service = app(CompaniesHouseService::class);
        $result  = $service->searchCompanies('Acme');

        expect($result['items'])->toHaveCount(2)
            ->and($result['total_results'])->toBe(2)
            ->and($result['items'][0]['company_number'])->toBe('12345678');
    });

    it('sends the query and items_per_page parameters in the request', function () {
        Http::fake([
            'api.company-information.service.gov.uk/search/companies*' => Http::response([
                'total_results' => 0,
                'items'         => [],
            ], 200),
        ]);

        $service = app(CompaniesHouseService::class);
        $service->searchCompanies('Pension Co', 5);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/search/companies')
                && $request['q'] === 'Pension Co'
                && (int) $request['items_per_page'] === 5;
        });
    });

    it('uses Basic Auth with the API key as the username', function () {
        Http::fake([
            'api.company-information.service.gov.uk/search/companies*' => Http::response([
                'total_results' => 0,
                'items'         => [],
            ], 200),
        ]);

        $service = app(CompaniesHouseService::class);
        $service->searchCompanies('Test Company');

        Http::assertSent(function ($request) {
            $authHeader = $request->header('Authorization')[0] ?? '';
            $decoded    = base64_decode(str_replace('Basic ', '', $authHeader));
            return str_starts_with($decoded, 'test-api-key:');
        });
    });

    it('returns empty items array when the API returns no results', function () {
        Http::fake([
            'api.company-information.service.gov.uk/search/companies*' => Http::response([
                'total_results' => 0,
                'items'         => [],
            ], 200),
        ]);

        $service = app(CompaniesHouseService::class);
        $result  = $service->searchCompanies('NoMatchCo');

        expect($result['items'])->toBeEmpty()
            ->and($result['total_results'])->toBe(0);
    });

    it('returns empty items array and logs a warning on a non-200 failure', function () {
        Http::fake([
            'api.company-information.service.gov.uk/search/companies*' => Http::response([], 503),
        ]);

        $service = app(CompaniesHouseService::class);
        $result  = $service->searchCompanies('Some Company');

        expect($result['items'])->toBeEmpty()
            ->and($result['total_results'])->toBe(0);
    });

    it('throws an exception on a 429 rate-limit response', function () {
        Http::fake([
            'api.company-information.service.gov.uk/search/companies*' => Http::response([], 429),
        ]);

        $service = app(CompaniesHouseService::class);

        expect(fn () => $service->searchCompanies('Acme'))
            ->toThrow(Exception::class, 'Companies House API rate limit exceeded.');
    });

    it('throws an exception when the API key is not configured', function () {
        config(['services.companies_house.api_key' => null]);

        $service = app(CompaniesHouseService::class);

        expect(fn () => $service->searchCompanies('Acme'))
            ->toThrow(Exception::class, 'Companies House API key is not configured.');
    });

    it('throws an exception on a connection error', function () {
        Http::fake([
            'api.company-information.service.gov.uk/search/companies*' => fn () => throw new ConnectionException('Connection refused'),
        ]);

        $service = app(CompaniesHouseService::class);

        expect(fn () => $service->searchCompanies('Acme'))
            ->toThrow(ConnectionException::class);
    });
});

// ── getCompany ────────────────────────────────────────────────────────────────

describe('CompaniesHouseService::getCompany', function () {

    it('returns full company details for a valid registration number', function () {
        Http::fake([
            'api.company-information.service.gov.uk/company/12345678' => Http::response([
                'company_number' => '12345678',
                'company_name'   => 'ACME LTD',
                'company_status' => 'active',
                'company_type'   => 'ltd',
                'sic_codes'      => ['62020'],
                'registered_office_address' => [
                    'address_line_1' => '1 Test Street',
                    'locality'       => 'London',
                    'postal_code'    => 'SW1A 1AA',
                ],
            ], 200),
        ]);

        $service = app(CompaniesHouseService::class);
        $result  = $service->getCompany('12345678');

        expect($result)->not->toBeNull()
            ->and($result['company_number'])->toBe('12345678')
            ->and($result['company_name'])->toBe('ACME LTD')
            ->and($result['company_status'])->toBe('active')
            ->and($result['registered_office_address']['postal_code'])->toBe('SW1A 1AA');
    });

    it('returns null when the company is not found (404)', function () {
        Http::fake([
            'api.company-information.service.gov.uk/company/00000000' => Http::response([], 404),
        ]);

        $service = app(CompaniesHouseService::class);
        $result  = $service->getCompany('00000000');

        expect($result)->toBeNull();
    });

    it('returns null and logs a warning on a server error', function () {
        Http::fake([
            'api.company-information.service.gov.uk/company/12345678' => Http::response([], 500),
        ]);

        $service = app(CompaniesHouseService::class);
        $result  = $service->getCompany('12345678');

        expect($result)->toBeNull();
    });

    it('throws an exception on a 429 rate-limit response', function () {
        Http::fake([
            'api.company-information.service.gov.uk/company/12345678' => Http::response([], 429),
        ]);

        $service = app(CompaniesHouseService::class);

        expect(fn () => $service->getCompany('12345678'))
            ->toThrow(Exception::class, 'Companies House API rate limit exceeded.');
    });

    it('throws an exception when the API key is not configured', function () {
        config(['services.companies_house.api_key' => null]);

        $service = app(CompaniesHouseService::class);

        expect(fn () => $service->getCompany('12345678'))
            ->toThrow(Exception::class, 'Companies House API key is not configured.');
    });

    it('throws an exception on a connection error', function () {
        Http::fake([
            'api.company-information.service.gov.uk/company/12345678' => fn () => throw new ConnectionException('Timeout'),
        ]);

        $service = app(CompaniesHouseService::class);

        expect(fn () => $service->getCompany('12345678'))
            ->toThrow(ConnectionException::class);
    });
});
