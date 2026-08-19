<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pivot table — an annotation can belong to zero, one, or several notebooks (PRD §4.2)
        Schema::create('annotation_notebook', function (Blueprint $table) {
            $table->id();
            $table->foreignId('annotation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('notebook_id')->constrained()->cascadeOnDelete();
            $table->timestamp('added_at')->useCurrent();

            $table->unique(['annotation_id', 'notebook_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('annotation_notebook');
    }
};
