<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onboarding_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('current_step')->default(1);
            $table->enum('status', ['in_progress', 'submitted'])->default('in_progress');

            // Personal details
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('previous_name')->nullable();
            $table->string('gender')->nullable();
            $table->unsignedTinyInteger('dob_day')->nullable();
            $table->string('dob_month')->nullable();
            $table->unsignedSmallInteger('dob_year')->nullable();
            $table->string('mobile')->nullable();
            $table->string('national_id')->nullable()->comment('Generic national identifier (NI, SSN, etc.)');

            // Address
            $table->string('address_postcode')->nullable();
            $table->string('address_house_number')->nullable();
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('address_city')->nullable();
            $table->string('proof_of_address_path')->nullable();

            // Signature (base64 PNG)
            $table->text('signature_data')->nullable();

            // Optional document approval flow (LoA, consent form, etc.)
            $table->longText('document_approval_content')->nullable();
            $table->string('document_approval_status')->nullable();
            $table->timestamp('document_approval_approved_at')->nullable();

            // Industry-specific extension data
            $table->json('metadata')->nullable();

            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onboarding_submissions');
    }
};
