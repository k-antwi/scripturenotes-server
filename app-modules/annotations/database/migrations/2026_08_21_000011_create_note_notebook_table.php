<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('note_notebook', function (Blueprint $table) {
            $table->id();
            $table->foreignId('note_id')->constrained('notes')->cascadeOnDelete();
            $table->foreignId('notebook_id')->constrained('notebooks')->cascadeOnDelete();
            $table->timestamp('added_at')->useCurrent();
            $table->unique(['note_id', 'notebook_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('note_notebook');
    }
};
