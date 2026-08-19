<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('book', 10);
            $table->unsignedSmallInteger('chapter');
            $table->unsignedSmallInteger('verse')->nullable();
            $table->string('label')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'book', 'chapter']);
            $table->unique(['user_id', 'book', 'chapter', 'verse']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookmarks');
    }
};
