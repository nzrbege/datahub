<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('download_pic_contacts', function (Blueprint $table) {
            $table->id();
            $table->string('nama_pic');
            $table->string('nomor_hp', 30);
            $table->text('keterangan')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('download_pic_contacts');
    }
};
