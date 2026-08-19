<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Outbox pattern — stores offline mutations awaiting server sync (PRD §4.2)
        Schema::create('sync_queue', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('entity_type', 30);   // annotation | notebook | bookmark | etc.
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->string('action', 10);          // create | update | delete
            $table->json('payload')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'synced_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_queue');
    }
};
