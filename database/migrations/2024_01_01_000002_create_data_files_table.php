<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // File data keluarga yang diupload super admin
        Schema::create('data_files', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->string('original_filename');
            $table->string('stored_filename'); // nama file terenkripsi di storage
            $table->string('file_path');
            $table->string('file_type'); // xlsx, csv, zip
            $table->bigInteger('file_size'); // bytes
            $table->string('file_hash'); // SHA-256 untuk integritas
            $table->boolean('is_encrypted')->default(true);
            $table->string('kategori')->nullable()->comment('Kategori data: KK, KTP, dll');
            $table->integer('jumlah_record')->nullable();
            $table->string('wilayah')->nullable();
            $table->string('tahun_data', 7)->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('restrict');
            $table->softDeletes();
            $table->timestamps();
        });

        // Tabel pivot: admin mana yang diizinkan akses file tertentu
        Schema::create('data_file_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('data_file_id')->constrained('data_files')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('granted_at');
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('granted_by')->constrained('users')->onDelete('restrict');
            $table->boolean('can_download')->default(true);
            $table->boolean('can_view_metadata')->default(true);
            $table->timestamps();
            $table->unique(['data_file_id', 'user_id']);
        });

        // Permintaan akses data dari admin
        Schema::create('data_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict'); // admin yang meminta
            $table->foreignId('data_file_id')->constrained('data_files')->onDelete('restrict');
            $table->text('alasan_permintaan');
            $table->text('tujuan_penggunaan');
            $table->string('dasar_hukum')->nullable()->comment('Dasar hukum sesuai UU PDP');
            
            // Dokumen perjanjian kerahasiaan
            $table->string('nda_filename')->nullable();
            $table->string('nda_path')->nullable();
            $table->string('nda_hash')->nullable();
            
            // Status approval
            $table->enum('status', ['pending', 'approved', 'rejected', 'revoked'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('catatan_reviewer')->nullable();
            
            // Token download sekali pakai
            $table->string('download_token')->nullable()->unique();
            $table->timestamp('token_expires_at')->nullable();
            $table->integer('download_count')->default(0);
            $table->integer('max_downloads')->default(3);
            $table->string('quota_period', 20)->default('weekly');
            $table->timestamp('quota_reset_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });

        // Log download setiap akses file
        Schema::create('download_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('data_file_id')->constrained('data_files')->onDelete('restrict');
            $table->foreignId('data_request_id')->nullable()->constrained('data_requests')->onDelete('restrict');
            $table->string('ip_address');
            $table->text('user_agent');
            $table->boolean('captcha_passed')->default(false);
            $table->enum('status', ['success', 'failed', 'blocked']);
            $table->text('keterangan')->nullable();
            $table->timestamp('downloaded_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('download_logs');
        Schema::dropIfExists('data_requests');
        Schema::dropIfExists('data_file_permissions');
        Schema::dropIfExists('data_files');
    }
};
