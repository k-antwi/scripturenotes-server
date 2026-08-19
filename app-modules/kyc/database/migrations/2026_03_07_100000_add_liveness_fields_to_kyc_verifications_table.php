<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kyc_verifications', function (Blueprint $table) {
            $table->string('liveness_video_path')->nullable()->after('name_change_uploaded_at');
            $table->timestamp('liveness_completed_at')->nullable()->after('liveness_video_path');
        });
    }

    public function down(): void
    {
        Schema::table('kyc_verifications', function (Blueprint $table) {
            $table->dropColumn([
                'liveness_video_path',
                'liveness_completed_at',
            ]);
        });
    }
};
