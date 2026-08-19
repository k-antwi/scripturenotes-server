<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kyc_verifications', function (Blueprint $table) {
            $table->string('proof_of_address_path')->nullable()->after('opt_in_submitted_at');
            $table->timestamp('proof_of_address_uploaded_at')->nullable()->after('proof_of_address_path');
            $table->string('photo_id_path')->nullable()->after('proof_of_address_uploaded_at');
            $table->timestamp('photo_id_uploaded_at')->nullable()->after('photo_id_path');
            $table->string('name_change_path')->nullable()->after('photo_id_uploaded_at');
            $table->timestamp('name_change_uploaded_at')->nullable()->after('name_change_path');
        });
    }

    public function down(): void
    {
        Schema::table('kyc_verifications', function (Blueprint $table) {
            $table->dropColumn([
                'proof_of_address_path',
                'proof_of_address_uploaded_at',
                'photo_id_path',
                'photo_id_uploaded_at',
                'name_change_path',
                'name_change_uploaded_at',
            ]);
        });
    }
};
