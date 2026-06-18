<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_files', function (Blueprint $table) {
            $table->string('tahun_data', 7)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('data_files', function (Blueprint $table) {
            $table->integer('tahun_data')->nullable()->change();
        });
    }
};
