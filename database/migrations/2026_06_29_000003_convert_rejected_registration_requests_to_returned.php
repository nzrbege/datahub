<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('user_registration_requests')
            ->where('status', 'rejected')
            ->update(['status' => 'returned']);
    }

    public function down(): void
    {
        DB::table('user_registration_requests')
            ->where('status', 'returned')
            ->update(['status' => 'rejected']);
    }
};
