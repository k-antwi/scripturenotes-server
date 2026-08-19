<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('annotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('book', 10);
            $table->unsignedSmallInteger('chapter');
            $table->unsignedSmallInteger('verse')->nullable();
            // Type: highlight | pen | note | underline | shape | custom_note (PRD §5.2)
            $table->string('type', 20);
            // Polymorphic data — stores stroke points, char ranges, or note text
            $table->json('data')->nullable();
            $table->string('colour', 20)->nullable();
            $table->boolean('is_shared')->default(false);
            $table->string('share_token', 64)->nullable()->unique();
            // Soft delete — PRD §7.2 DELETE is soft-delete on the server
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'book', 'chapter']);
            $table->index(['user_id', 'type']);
            $table->index('share_token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('annotations');
    }
};
