<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Buat roles
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $adminRole      = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        // Permissions
        $permissions = [
            'upload_data_files',
            'delete_data_files',
            'manage_users',
            'manage_file_permissions',
            'approve_data_requests',
            'view_audit_logs',
            'request_data_access',
            'download_data_files',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // Super Admin mendapat semua permission
        $superAdminRole->syncPermissions(Permission::all());

        // Admin hanya bisa request dan download
        $adminRole->syncPermissions(['request_data_access', 'download_data_files']);

        // Buat Super Admin default
        $superAdmin = User::firstOrCreate(
            ['username' => 'superadmin'],
            [
                'name'              => 'Super Administrator',
                'email'             => 'superadmin@datakeluarga.go.id',
                'password'          => Hash::make('SuperAdmin@12345!'),
                'instansi'          => 'Pusat Data Kependudukan',
                'jabatan'           => 'Administrator Sistem',
                'is_active'         => true,
                'email_verified_at' => now(),
            ]
        );
        $superAdmin->assignRole('super_admin');

        $this->command->info('✅ Seeder berhasil dijalankan.');
        $this->command->warn('⚠️  Segera ganti password default Super Admin setelah login pertama!');
        $this->command->line('   Username: superadmin');
        $this->command->line('   Pass : SuperAdmin@12345!');
    }
}
