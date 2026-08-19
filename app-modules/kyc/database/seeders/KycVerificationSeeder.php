<?php

namespace Nucleus\Kyc\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Nucleus\Kyc\Models\KycVerification;

class KycVerificationSeeder extends Seeder
{
    /**
     * Seeds KYC verification records for demo users.
     * Admin user (id=1) gets a pre-verified record so they can access the app immediately.
     */
    public function run(): void
    {
        $admin = User::find(1) ?? User::first();

        if (! $admin) {
            $this->command->warn('KycVerificationSeeder: No users found — skipping.');
            return;
        }

        // Admin user: pre-verified so they land on the dashboard
        KycVerification::updateOrCreate(
            ['user_id' => $admin->id],
            [
                'status'                       => 'verified',
                'email_verified_at'            => now()->subMinutes(5),
                'identity_verified_at'         => now()->subMinutes(4),
                'proof_of_address_path'        => 'seeded',
                'proof_of_address_uploaded_at'  => now()->subMinutes(3),
                'photo_id_path'                => 'seeded',
                'photo_id_uploaded_at'          => now()->subMinutes(3),
                'name_change_path'             => 'seeded',
                'name_change_uploaded_at'      => now()->subMinutes(3),
                'liveness_video_path'          => 'seeded',
                'liveness_completed_at'        => now()->subMinutes(2),
            ]
        );

        $this->command->info("KYC: Admin user ({$admin->email}) marked as verified.");
    }
}
