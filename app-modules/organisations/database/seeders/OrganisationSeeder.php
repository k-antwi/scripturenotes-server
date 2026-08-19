<?php

namespace Nucleus\Organisations\Database\Seeders;

use Illuminate\Database\Seeder;
use Nucleus\Organisations\Models\Organisation;

class OrganisationSeeder extends Seeder
{
    public function run(): void
    {
        $organisations = [
            [
                'name'                  => 'Acme Corporation Ltd',
                'legal_name'            => 'Acme Corporation Limited',
                'registration_number'   => '12345678',
                'tax_reference'         => '123/AB45678',
                'organisation_type'     => 'employer',
                'status'                => 'active',
                'industry_code'         => '70100',
                'employee_count'        => 250,
                'primary_contact_name'  => 'Jane Smith',
                'primary_contact_email' => 'hr@acme.example',
                'primary_contact_phone' => '020 7946 0001',
                'address_line_1'        => '1 Business Park',
                'city'                  => 'London',
                'postcode'              => 'EC1A 1BB',
                'country_code'          => 'GB',
            ],
            [
                'name'                  => 'Northwind Partners',
                'registration_number'   => '87654321',
                'organisation_type'     => 'partner',
                'status'                => 'active',
                'employee_count'        => 12,
                'primary_contact_name'  => 'Robert Jones',
                'primary_contact_email' => 'partners@northwind.example',
                'primary_contact_phone' => '0161 496 0002',
                'address_line_1'        => '10 Partner Street',
                'city'                  => 'Manchester',
                'postcode'              => 'M1 2AB',
                'country_code'          => 'GB',
            ],
        ];

        foreach ($organisations as $data) {
            Organisation::firstOrCreate(
                ['registration_number' => $data['registration_number'] ?? null, 'name' => $data['name']],
                $data
            );
        }
    }
}
