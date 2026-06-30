<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_registration_requests', function (Blueprint $table) {
            $table->string('username')->nullable()->after('name')->index();
        });
    }

    public function down(): void
    {
        Schema::table('user_registration_requests', function (Blueprint $table) {
            $table->dropIndex(['username']);
            $table->dropColumn('username');
        });
    }
};
