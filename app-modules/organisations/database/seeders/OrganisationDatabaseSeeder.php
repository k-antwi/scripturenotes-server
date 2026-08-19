<?php

namespace Nucleus\Organisations\Database\Seeders;

use Illuminate\Database\Seeder;

class OrganisationDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(OrganisationSeeder::class);
    }
}
