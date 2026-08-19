<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('passages', function (Blueprint $table) {
            $table->id();
            // Keyed by book+chapter+translation (PRD §4.2)
            $table->string('book', 10);
            $table->unsignedSmallInteger('chapter');
            $table->string('translation', 10)->default('KJV');
            // Full API response cached as JSON; includes verses, reference, footnotes
            $table->json('content');
            $table->timestamp('fetched_at');
            $table->timestamps();

            $table->unique(['book', 'chapter', 'translation']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('passages');
    }
};
