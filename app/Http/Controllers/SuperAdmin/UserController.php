<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct(private AuditService $audit) {}

    public function index()
    {
        $users = User::with('roles')->withTrashed()->latest()->paginate(20);
        return view('superadmin.users.index', compact('users'));
    }

    public function create()
    {
        return view('superadmin.users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'username'  => ['required', 'string', 'max:255', 'alpha_dash', 'unique:users,username'],
            'email'     => ['required', 'email', 'unique:users,email'],
            'password'  => ['required', 'string', 'min:12', 'confirmed',
                            'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#]).{12,}$/'],
            'phone'     => ['nullable', 'string', 'max:20'],
            'instansi'  => ['required', 'string', 'max:255'],
            'jabatan'   => ['required', 'string', 'max:255'],
            'role'      => ['required', 'in:admin,super_admin'],
        ], [
            'password.regex' => 'Kata sandi harus minimal 12 karakter, mengandung huruf besar, kecil, angka, dan karakter khusus.',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'username' => $request->username,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'phone'    => $request->phone,
            'instansi' => $request->instansi,
            'jabatan'  => $request->jabatan,
            'is_active'=> true,
        ]);

        $user->assignRole($request->role);

        $this->audit->log(AuditService::ACTION_USER_CREATE, $user, [
            'role'     => $request->role,
            'instansi' => $request->instansi,
        ]);

        return redirect()->route('superadmin.users.index')
            ->with('success', "Pengguna {$user->name} berhasil dibuat.");
    }

    public function edit(User $user)
    {
        $user->load('roles');
        return view('superadmin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('users', 'username')->ignore($user->id)],
            'phone'    => ['nullable', 'string', 'max:20'],
            'instansi' => ['required', 'string', 'max:255'],
            'jabatan'  => ['required', 'string', 'max:255'],
            'role'     => ['required', 'in:admin,super_admin'],
        ]);

        $user->update([
            'name'     => $request->name,
            'username' => $request->username,
            'phone'    => $request->phone,
            'instansi' => $request->instansi,
            'jabatan'  => $request->jabatan,
        ]);

        $user->syncRoles([$request->role]);

        $this->audit->log(AuditService::ACTION_USER_UPDATE, $user, [
            'changes' => $request->only('name', 'username', 'instansi', 'jabatan', 'role'),
        ]);

        return redirect()->route('superadmin.users.index')
            ->with('success', "Data pengguna berhasil diperbarui.");
    }

    public function toggleActive(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'Tidak dapat menonaktifkan akun sendiri.']);
        }

        $user->update(['is_active' => !$user->is_active]);

        $this->audit->log(AuditService::ACTION_USER_DEACTIVATE, $user, [
            'new_status' => $user->is_active ? 'active' : 'inactive',
        ]);

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Pengguna berhasil {$status}.");
    }

    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'password' => ['required', 'string', 'min:12', 'confirmed',
                          'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#]).{12,}$/'],
        ]);

        $user->update(['password' => Hash::make($request->password)]);

        $this->audit->log('password_reset', $user, ['reset_by' => auth()->id()]);

        return back()->with('success', 'Kata sandi berhasil direset.');
    }
}
