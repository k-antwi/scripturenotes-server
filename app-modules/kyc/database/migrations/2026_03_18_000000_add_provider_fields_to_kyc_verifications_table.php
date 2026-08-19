<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kyc_verifications', function (Blueprint $table) {
            // The KYC provider used for this verification (e.g. 'manual', 'stripe', 'onfido', 'jumio', 'veriff')
            $table->string('provider')->default('manual')->after('user_id');

            // The session / applicant / transaction ID returned by the external provider
            $table->string('provider_reference')->nullable()->after('provider');

            // Arbitrary JSON bag for provider-specific metadata (redirect URLs, tokens, etc.)
            $table->json('provider_metadata')->nullable()->after('provider_reference');
        });
    }

    public function down(): void
    {
        Schema::table('kyc_verifications', function (Blueprint $table) {
            $table->dropColumn(['provider', 'provider_reference', 'provider_metadata']);
        });
    }
};
