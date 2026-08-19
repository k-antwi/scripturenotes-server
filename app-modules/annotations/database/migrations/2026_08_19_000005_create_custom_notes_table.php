<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // User-authored commentary — independent of third-party datasets (PRD §4.2)
        Schema::create('custom_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('book', 10);
            $table->unsignedSmallInteger('chapter');
            $table->unsignedSmallInteger('verse')->nullable();
            $table->string('title')->nullable();
            $table->longText('body');
            $table->timestamps();

            $table->index(['user_id', 'book', 'chapter']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_notes');
    }
};
