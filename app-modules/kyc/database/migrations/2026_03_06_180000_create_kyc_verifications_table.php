<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kyc_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->enum('status', [
                'pending',
                'email_verified',
                'identity_verifying',
                'identity_slow',
                'verified',
                'failed',
                'failed_submitted',
            ])->default('pending');
            $table->string('failure_reason')->nullable();
            $table->boolean('opt_in_notification')->default(false);
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('identity_verified_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('opt_in_submitted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kyc_verifications');
    }
};
