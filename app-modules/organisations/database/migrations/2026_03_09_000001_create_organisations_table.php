<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organisations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('legal_name')->nullable();
            $table->string('registration_number')->nullable()->comment('Companies House / charity number');
            $table->string('tax_reference')->nullable()->comment('Tax / payroll reference');
            $table->enum('organisation_type', ['employer', 'provider', 'partner', 'other'])->default('other');
            $table->enum('status', ['active', 'suspended', 'dissolved'])->default('active');
            $table->string('industry_code')->nullable()->comment('SIC / NAICS / industry classifier');
            $table->unsignedInteger('employee_count')->nullable();
            $table->foreignId('parent_organisation_id')->nullable()->constrained('organisations')->nullOnDelete();
            $table->string('primary_contact_name')->nullable();
            $table->string('primary_contact_email')->nullable();
            $table->string('primary_contact_phone')->nullable();
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city')->nullable();
            $table->string('county')->nullable();
            $table->string('postcode')->nullable();
            $table->string('country_code', 2)->nullable()->comment('ISO 3166-1 alpha-2');
            $table->json('metadata')->nullable()->comment('JSON blob for industry-specific extensions');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organisations');
    }
};
