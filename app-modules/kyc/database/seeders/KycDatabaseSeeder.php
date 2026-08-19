<?php

namespace Nucleus\Kyc\Database\Seeders;

use Illuminate\Database\Seeder;

class KycDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(KycVerificationSeeder::class);
    }
}
