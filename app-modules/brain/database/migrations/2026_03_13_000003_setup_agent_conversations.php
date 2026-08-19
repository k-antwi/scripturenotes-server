<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the Laravel AI SDK conversation tables (agent_conversations,
 * agent_conversation_messages) and drops the old brain_conversations table.
 *
 * These tables are normally published via:
 *   php artisan vendor:publish --tag=ai-migrations
 * This migration ensures they exist when running Brain module migrations.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('agent_conversations')) {
            Schema::create('agent_conversations', function (Blueprint $table) {
                $table->string('id', 36)->primary();
                $table->foreignId('user_id')->nullable();
                $table->string('title');
                $table->timestamps();

                $table->index(['user_id', 'updated_at']);
            });
        }

        if (! Schema::hasTable('agent_conversation_messages')) {
            Schema::create('agent_conversation_messages', function (Blueprint $table) {
                $table->string('id', 36)->primary();
                $table->string('conversation_id', 36)->index();
                $table->foreignId('user_id')->nullable();
                $table->string('agent');
                $table->string('role', 25);
                $table->text('content');
                $table->text('attachments');
                $table->text('tool_calls');
                $table->text('tool_results');
                $table->text('usage');
                $table->text('meta');
                $table->timestamps();

                $table->index(['conversation_id', 'user_id', 'updated_at'], 'conversation_index');
                $table->index(['user_id']);
            });
        }

        // Drop the old Brain-specific conversation table now that the
        // Laravel AI SDK's tables handle conversation persistence.
        Schema::dropIfExists('brain_conversations');
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_conversations');
        Schema::dropIfExists('agent_conversation_messages');

        Schema::create('brain_conversations', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable();
            $table->string('model_id');
            $table->string('context_type', 20)->default('user');
            $table->json('messages');
            $table->timestamps();

            $table->index(['context_type', 'user_id', 'updated_at']);
        });
    }
};
