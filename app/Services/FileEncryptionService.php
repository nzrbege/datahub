<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Service enkripsi file sesuai UU PDP No.27/2022 Pasal 35
 * Kewajiban melindungi data pribadi dengan enkripsi.
 */
class FileEncryptionService
{
    private string $cipher = 'AES-256-CBC';

    private function getKey(): string
    {
        $key = config('app.file_encryption_key', env('FILE_ENCRYPTION_KEY'));
        if (empty($key)) {
            // fallback ke app key
            $key = base64_decode(str_replace('base64:', '', config('app.key')));
        }
        if (strlen($key) < 32) {
            $key = str_pad($key, 32, '0');
        }
        return substr($key, 0, 32);
    }

    /**
     * Enkripsi file dan simpan ke storage private.
     * Returns stored filename.
     */
    public function encryptAndStore(string $sourcePath, string $diskPath): bool
    {
        $key       = $this->getKey();
        $iv        = random_bytes(16);
        $contents  = file_get_contents($sourcePath);

        if ($contents === false) {
            throw new RuntimeException("Tidak bisa membaca file: {$sourcePath}");
        }

        $encrypted = openssl_encrypt($contents, $this->cipher, $key, OPENSSL_RAW_DATA, $iv);

        if ($encrypted === false) {
            throw new RuntimeException("Enkripsi gagal.");
        }

        // Format: [IV 16 bytes][HMAC 32 bytes][ciphertext]
        $mac      = hash_hmac('sha256', $iv . $encrypted, $key, true);
        $payload  = $iv . $mac . $encrypted;

        return Storage::disk('private')->put($diskPath, $payload);
    }

    /**
     * Dekripsi file dari storage dan kembalikan isi aslinya.
     */
    public function decryptFromStorage(string $diskPath): string
    {
        $payload = Storage::disk('private')->get($diskPath);

        if ($payload === null) {
            throw new RuntimeException("File tidak ditemukan di storage.");
        }

        $key     = $this->getKey();
        $iv      = substr($payload, 0, 16);
        $mac     = substr($payload, 16, 32);
        $cipher  = substr($payload, 48);

        // Verify HMAC sebelum dekripsi (tamper-detection)
        $expectedMac = hash_hmac('sha256', $iv . $cipher, $key, true);
        if (!hash_equals($mac, $expectedMac)) {
            throw new RuntimeException("Integritas file gagal: file mungkin telah dimodifikasi.");
        }

        $decrypted = openssl_decrypt($cipher, $this->cipher, $key, OPENSSL_RAW_DATA, $iv);

        if ($decrypted === false) {
            throw new RuntimeException("Dekripsi gagal.");
        }

        return $decrypted;
    }

    /**
     * Hitung hash SHA-256 untuk integritas file (UU PDP).
     */
    public function computeHash(string $filePath): string
    {
        return hash_file('sha256', $filePath);
    }

    /**
     * Generate stored filename yang aman (tidak mengekspos nama asli).
     */
    public function generateStoredFilename(string $extension): string
    {
        return Str::random(40) . '.' . $extension . '.enc';
    }
}
