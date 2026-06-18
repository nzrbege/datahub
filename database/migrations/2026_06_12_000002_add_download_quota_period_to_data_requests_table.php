<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_requests', function (Blueprint $table) {
            $table->string('quota_period', 20)->default('weekly')->after('max_downloads');
            $table->timestamp('quota_reset_at')->nullable()->after('quota_period');
        });
    }

    public function down(): void
    {
        Schema::table('data_requests', function (Blueprint $table) {
            $table->dropColumn(['quota_period', 'quota_reset_at']);
        });
    }
};
