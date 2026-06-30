<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_requests', function (Blueprint $table) {
            $table->string('bast_filename')->nullable()->after('nda_hash');
            $table->string('bast_path')->nullable()->after('bast_filename');
            $table->string('bast_hash')->nullable()->after('bast_path');
            $table->foreignId('bast_reviewed_by')->nullable()->after('catatan_reviewer')->constrained('users')->onDelete('restrict');
            $table->timestamp('bast_reviewed_at')->nullable()->after('bast_reviewed_by');
            $table->text('catatan_bast')->nullable()->after('bast_reviewed_at');
        });

        DB::statement("ALTER TABLE data_requests MODIFY status ENUM('pending','returned','approved','bast_pending','bast_approved','bast_rejected','rejected','revoked') DEFAULT 'pending'");
        DB::table('data_requests')->where('status', 'approved')->update(['status' => 'bast_approved']);

    }

    public function down(): void
    {
        DB::table('data_requests')->whereIn('status', ['returned', 'bast_pending', 'bast_rejected'])->update(['status' => 'pending']);
        DB::table('data_requests')->where('status', 'bast_approved')->update(['status' => 'approved']);
        DB::statement("ALTER TABLE data_requests MODIFY status ENUM('pending','approved','rejected','revoked') DEFAULT 'pending'");

        Schema::table('data_requests', function (Blueprint $table) {
            $table->dropForeign(['bast_reviewed_by']);
            $table->dropColumn([
                'bast_filename',
                'bast_path',
                'bast_hash',
                'bast_reviewed_by',
                'bast_reviewed_at',
                'catatan_bast',
            ]);
        });
    }
};
