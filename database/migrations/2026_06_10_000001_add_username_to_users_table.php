<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'username')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('username')->nullable()->after('name');
            });
        }

        $usedUsernames = [];

        DB::table('users')
            ->select('id', 'email', 'username')
            ->orderBy('id')
            ->get()
            ->each(function ($user) use (&$usedUsernames) {
                if ($user->username) {
                    $usedUsernames[$user->username] = true;
                    return;
                }

                $emailPrefix = Str::before((string) $user->email, '@');
                $baseUsername = Str::of($emailPrefix)
                    ->lower()
                    ->replaceMatches('/[^a-z0-9_-]/', '_')
                    ->trim('_')
                    ->value() ?: 'user';

                $username = $baseUsername;
                $suffix = 1;

                while (isset($usedUsernames[$username])) {
                    $username = $baseUsername . '_' . $suffix++;
                }

                $usedUsernames[$username] = true;

                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['username' => $username]);
            });

        Schema::table('users', function (Blueprint $table) {
            $table->unique('username');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'username')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique(['username']);
                $table->dropColumn('username');
            });
        }
    }
};
