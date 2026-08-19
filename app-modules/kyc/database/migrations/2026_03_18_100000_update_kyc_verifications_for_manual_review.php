<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Replace the status enum with new values by dropping and recreating the column.
        // Two separate Schema::table() calls are required for SQLite compatibility.
        Schema::table('kyc_verifications', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('kyc_verifications', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('provider_metadata');

            // ── Per-check results ────────────────────────────────────────────
            // result: pending | passed | failed | more_info
            $table->string('proof_of_address_result')->default('pending')->after('proof_of_address_uploaded_at');
            $table->text('proof_of_address_note')->nullable()->after('proof_of_address_result');

            $table->string('photo_id_result')->default('pending')->after('photo_id_uploaded_at');
            $table->text('photo_id_note')->nullable()->after('photo_id_result');

            $table->string('name_change_result')->default('pending')->after('name_change_uploaded_at');
            $table->text('name_change_note')->nullable()->after('name_change_result');

            $table->string('liveness_result')->default('pending')->after('liveness_completed_at');
            $table->text('liveness_note')->nullable()->after('liveness_result');

            // ── Admin / review metadata ───────────────────────────────────────
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('overall_review_note')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('review_started_at')->nullable();
            $table->timestamp('review_completed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('kyc_verifications', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'proof_of_address_result',
                'proof_of_address_note',
                'photo_id_result',
                'photo_id_note',
                'name_change_result',
                'name_change_note',
                'liveness_result',
                'liveness_note',
                'reviewed_by',
                'overall_review_note',
                'submitted_at',
                'review_started_at',
                'review_completed_at',
            ]);
        });

        Schema::table('kyc_verifications', function (Blueprint $table) {
            $table->enum('status', [
                'pending',
                'email_verified',
                'identity_verifying',
                'identity_slow',
                'verified',
                'failed',
                'failed_submitted',
            ])->default('pending')->after('provider_metadata');
        });
    }
};
