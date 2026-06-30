<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Opd;
use App\Models\User;
use App\Models\UserRegistrationRequest;
use App\Services\AuditService;
use App\Support\Security\FileResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RegistrationRequestController extends Controller
{
    public function __construct(private AuditService $audit) {}

    public function create()
    {
        return view('auth.register-request', [
            'opdOptions' => $this->opdOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $opdOptions = $this->opdOptions();

        $validated = $request->validate([
            'instansi' => ['required', 'string', 'max:255', Rule::in($opdOptions->all())],
            'username' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9._-]+$/'],
            'email' => ['required', 'email', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'letter_file' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ], [
            'instansi.required' => 'Instansi/OPD wajib diisi.',
            'instansi.in' => 'Pilih Instansi/OPD dari daftar yang tersedia.',
            'username.required' => 'Username wajib diisi.',
            'username.regex' => 'Username hanya boleh berisi huruf, angka, titik, garis bawah, dan strip.',
            'phone.required' => 'No HP wajib diisi.',
            'letter_file.required' => 'Surat permohonan wajib dilampirkan.',
            'letter_file.mimes' => 'Surat permohonan harus berupa PDF.',
            'letter_file.max' => 'Ukuran surat permohonan maksimal 10MB.',
        ]);

        $username = Str::of($validated['username'])
            ->lower()
            ->trim()
            ->value();
        $email = strtolower(trim($validated['email']));

        if (User::where('username', $username)->exists()) {
            throw ValidationException::withMessages([
                'username' => 'Username ini sudah digunakan.',
            ]);
        }

        if (User::where('email', $email)->exists()) {
            throw ValidationException::withMessages([
                'email' => 'Email ini sudah terdaftar sebagai pengguna.',
            ]);
        }

        $activeRequestExists = UserRegistrationRequest::where('email', $email)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($activeRequestExists) {
            throw ValidationException::withMessages([
                'email' => 'Permohonan registrasi untuk email ini sudah pernah diajukan.',
            ]);
        }

        $activeUsernameRequestExists = UserRegistrationRequest::where('username', $username)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($activeUsernameRequestExists) {
            throw ValidationException::withMessages([
                'username' => 'Permohonan registrasi untuk username ini sudah pernah diajukan.',
            ]);
        }

        $file = $request->file('letter_file');
        $hash = hash_file('sha256', $file->getRealPath());
        $storedName = 'registrasi_user_' . now()->format('YmdHis') . '_' . Str::random(10) . '.pdf';
        $path = $file->storeAs('user-registration-requests', $storedName, 'private');

        $registrationRequest = UserRegistrationRequest::create([
            'name' => $validated['name'],
            'username' => $username,
            'email' => $email,
            'phone' => $validated['phone'],
            'instansi' => $validated['instansi'],
            'letter_filename' => FileResponse::safeFilename($file->getClientOriginalName(), 'surat-permohonan.pdf'),
            'letter_path' => $path,
            'letter_hash' => $hash,
            'status' => 'pending',
        ]);

        $this->audit->log('user_registration_request_create', $registrationRequest, [
            'email' => $registrationRequest->email,
            'username' => $registrationRequest->username,
            'instansi' => $registrationRequest->instansi,
        ]);

        $this->notifySuperAdmins($registrationRequest);

        return redirect()->route('register.request')
            ->with('success', 'Permohonan registrasi berhasil dikirim. Silakan menunggu verifikasi Super Admin.');
    }

    private function notifySuperAdmins(UserRegistrationRequest $registrationRequest): void
    {
        $configuredRecipients = collect(explode(',', (string) config('mail.registration_notifications.to')))
            ->map(fn ($email) => strtolower(trim($email)))
            ->filter();

        $superAdminRecipients = User::role('super_admin')
            ->whereNotNull('email')
            ->where('is_active', true)
            ->pluck('email')
            ->map(fn ($email) => strtolower(trim($email)))
            ->filter();

        $recipients = $configuredRecipients
            ->merge($superAdminRecipients)
            ->filter()
            ->unique()
            ->values();

        if ($recipients->isEmpty()) {
            Log::warning('Email notifikasi registrasi user tidak dikirim karena penerima kosong.', [
                'registration_request_id' => $registrationRequest->id,
            ]);

            return;
        }

        try {
            Mail::raw(
                "Ada permohonan registrasi user baru.\n\n" .
                "Nama: {$registrationRequest->name}\n" .
                "Username: {$registrationRequest->username}\n" .
                "Instansi/OPD: {$registrationRequest->instansi}\n" .
                "Email: {$registrationRequest->email}\n" .
                "No HP: {$registrationRequest->phone}\n\n" .
                "Silakan login sebagai Super Admin untuk melakukan verifikasi.",
                function ($message) use ($recipients) {
                    $message->to($recipients->all())
                        ->subject('Permohonan Registrasi User Baru');
                }
            );
        } catch (\Throwable $exception) {
            Log::warning('Gagal mengirim email notifikasi registrasi user.', [
                'registration_request_id' => $registrationRequest->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function opdOptions()
    {
        return Opd::query()
            ->where('is_active', true)
            ->orderBy('nama')
            ->pluck('nama')
            ->filter()
            ->values();
    }
}
