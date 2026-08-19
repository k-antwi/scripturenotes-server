<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add the optional self-FK linking a consumer User to their assigned
     * Provider User.
     *
     * Keep this column nullable. Marketplace / multi-provider industries
     * (legal firms with several attorneys per matter, multi-doctor patient
     * care) need to ignore the column rather than have it enforced.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('provider_id')
                ->nullable()
                ->after('id')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('provider_id');
        });
    }
};
