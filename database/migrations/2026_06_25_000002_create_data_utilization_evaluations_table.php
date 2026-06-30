<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_utilization_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('data_request_id')->constrained('data_requests')->onDelete('cascade');
            $table->foreignId('data_file_id')->constrained('data_files')->onDelete('restrict');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->string('report_filename');
            $table->string('report_path');
            $table->string('report_hash');
            $table->text('notes')->nullable();
            $table->timestamp('submitted_at');
            $table->timestamps();

            $table->unique('data_request_id');
            $table->index(['user_id', 'submitted_at']);
            $table->index(['data_file_id', 'submitted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_utilization_evaluations');
    }
};
