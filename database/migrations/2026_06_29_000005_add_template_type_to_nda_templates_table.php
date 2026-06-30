<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nda_templates', function (Blueprint $table) {
            $table->string('template_type', 40)->default('bast')->after('id')->index();
        });

        DB::table('nda_templates')->update(['template_type' => 'bast']);
    }

    public function down(): void
    {
        Schema::table('nda_templates', function (Blueprint $table) {
            $table->dropIndex(['template_type']);
            $table->dropColumn('template_type');
        });
    }
};
