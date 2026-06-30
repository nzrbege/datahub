<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserRegistrationRequest;
use App\Services\AuditService;
use App\Support\Security\FileResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class UserRegistrationRequestController extends Controller
{
    public function __construct(private AuditService $audit) {}

    public function index(Request $request)
    {
        $query = UserRegistrationRequest::with(['reviewer', 'createdUser'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $term = $request->q;
            $query->where(function ($inner) use ($term) {
                $inner->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('instansi', 'like', "%{$term}%");
            });
        }

        $requests = $query->paginate(20)->withQueryString();
        $statusCounts = UserRegistrationRequest::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('superadmin.user-registrations.index', compact('requests', 'statusCounts'));
    }

    public function letter(UserRegistrationRequest $registration)
    {
        if (!Storage::disk('private')->exists($registration->letter_path)) {
            abort(404, 'Surat permohonan tidak ditemukan.');
        }

        return response()->file(Storage::disk('private')->path($registration->letter_path), [
            'Content-Disposition' => FileResponse::inlineDisposition($registration->letter_filename, 'surat-permohonan.pdf'),
        ]);
    }

    public function approve(Request $request, UserRegistrationRequest $registration)
    {
        if (!$registration->isPending()) {
            return back()->withErrors(['error' => 'Permohonan ini sudah diverifikasi.']);
        }

        $validated = $request->validate([
            'password' => ['required', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols()],
            'review_notes' => ['nullable', 'string', 'max:2000'],
        ], [
            'password.min' => 'Password awal minimal 12 karakter.',
            'password.mixed' => 'Password awal harus berisi huruf besar dan huruf kecil.',
            'password.numbers' => 'Password awal harus berisi angka.',
            'password.symbols' => 'Password awal harus berisi karakter khusus.',
        ]);

        if (User::where('email', $registration->email)->exists()) {
            return back()->withErrors(['error' => 'Email pemohon sudah terdaftar sebagai pengguna.']);
        }

        $username = $registration->username ?: $this->uniqueUsernameFromEmail($registration->email);

        if (User::where('username', $username)->exists()) {
            return back()->withErrors(['error' => 'Username pemohon sudah digunakan sebagai pengguna.']);
        }

        $user = User::create([
            'name' => $registration->name,
            'username' => $username,
            'email' => $registration->email,
            'phone' => $registration->phone,
            'instansi' => $registration->instansi,
            'jabatan' => 'Admin OPD',
            'password' => Hash::make($validated['password']),
            'is_active' => true,
        ]);

        $user->assignRole('admin');

        $registration->update([
            'status' => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'review_notes' => $validated['review_notes'] ?? null,
            'created_user_id' => $user->id,
        ]);

        $this->audit->log('user_registration_request_approve', $registration, [
            'created_user_id' => $user->id,
            'email' => $registration->email,
        ]);

        $emailSent = $this->notifyApprovedUser($registration, $user, $validated['password']);

        if (!$emailSent) {
            return back()->with('warning', "Permohonan {$registration->name} disetujui dan akun admin OPD berhasil dibuat, tetapi email pemberitahuan ke user gagal dikirim. Cek log aplikasi.");
        }

        return back()->with('success', "Permohonan {$registration->name} disetujui dan akun admin OPD berhasil dibuat.");
    }

    public function reject(Request $request, UserRegistrationRequest $registration)
    {
        if (!$registration->isPending()) {
            return back()->withErrors(['error' => 'Permohonan ini sudah diverifikasi.']);
        }

        $validated = $request->validate([
            'review_notes' => ['required', 'string', 'max:2000'],
        ], [
            'review_notes.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $registration->update([
            'status' => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'review_notes' => $validated['review_notes'],
        ]);

        $this->audit->log('user_registration_request_reject', $registration, [
            'email' => $registration->email,
            'review_notes' => $registration->review_notes,
        ]);

        $emailSent = $this->notifyRejectedUser($registration);

        if (!$emailSent) {
            return back()->with('warning', "Permohonan {$registration->name} ditolak, tetapi email pemberitahuan ke user gagal dikirim. Cek log aplikasi.");
        }

        return back()->with('success', "Permohonan {$registration->name} ditolak. Pemohon dapat mengajukan ulang dari form registrasi.");
    }

    private function uniqueUsernameFromEmail(string $email): string
    {
        $base = Str::of(Str::before($email, '@'))
            ->lower()
            ->replaceMatches('/[^a-z0-9_-]/', '_')
            ->trim('_')
            ->value() ?: 'user';

        $username = $base;
        $suffix = 1;

        while (User::where('username', $username)->exists()) {
            $username = $base . '_' . $suffix++;
        }

        return $username;
    }

    private function notifyApprovedUser(UserRegistrationRequest $registration, User $user, string $plainPassword): bool
    {
        try {
            Mail::raw(
                "Yth. {$registration->name},\n\n" .
                "Permohonan registrasi akun Anda telah disetujui.\n\n" .
                "Detail akun:\n" .
                "Nama: {$user->name}\n" .
                "Instansi/OPD: {$user->instansi}\n" .
                "Username: {$user->username}\n" .
                "Email: {$user->email}\n" .
                "Password awal: {$plainPassword}\n\n" .
                "Silakan login melalui " . config('app.url') . " dan segera ganti password setelah berhasil masuk.\n\n" .
                "Terima kasih.",
                function ($message) use ($user) {
                    $message->to($user->email)
                        ->subject('Akun DTSEN Anda Telah Disetujui');
                }
            );

            return true;
        } catch (\Throwable $exception) {
            Log::warning('Gagal mengirim email persetujuan registrasi user.', [
                'registration_request_id' => $registration->id,
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    private function notifyRejectedUser(UserRegistrationRequest $registration): bool
    {
        try {
            Mail::raw(
                "Yth. {$registration->name},\n\n" .
                "Permohonan registrasi akun Anda ditolak.\n\n" .
                "Detail permohonan:\n" .
                "Nama: {$registration->name}\n" .
                "Username: {$registration->username}\n" .
                "Instansi/OPD: {$registration->instansi}\n" .
                "Email: {$registration->email}\n\n" .
                "Alasan penolakan:\n" .
                "{$registration->review_notes}\n\n" .
                "Silakan ajukan permohonan registrasi baru melalui form registrasi jika data atau dokumen sudah diperbaiki.\n\n" .
                "Terima kasih.",
                function ($message) use ($registration) {
                    $message->to($registration->email)
                        ->subject('Permohonan Registrasi DTSEN Ditolak');
                }
            );

            return true;
        } catch (\Throwable $exception) {
            Log::warning('Gagal mengirim email penolakan registrasi user.', [
                'registration_request_id' => $registration->id,
                'email' => $registration->email,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }
}
