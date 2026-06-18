<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class FileStorageService
{
    private const CHUNK_SIZE = 1048576; // 1 MB

    /**
     * Simpan file asli ke storage private tanpa proses tambahan aplikasi.
     */
    public function storePrivate(string $sourcePath, string $diskPath): bool
    {
        $stream = fopen($sourcePath, 'rb');

        if ($stream === false) {
            throw new RuntimeException("Tidak bisa membaca file: {$sourcePath}");
        }

        try {
            return Storage::disk('private')->put($diskPath, $stream);
        } finally {
            fclose($stream);
        }
    }

    /**
     * Stream file dari storage private tanpa memuat seluruh file ke memory.
     */
    public function streamFromStorage(string $diskPath, callable $write): void
    {
        $stream = Storage::disk('private')->readStream($diskPath);

        if ($stream === false) {
            throw new RuntimeException("File tidak ditemukan di storage.");
        }

        try {
            while (!feof($stream)) {
                $chunk = fread($stream, self::CHUNK_SIZE);

                if ($chunk === false) {
                    throw new RuntimeException("Gagal membaca file dari storage.");
                }

                if ($chunk !== '') {
                    $write($chunk);
                }
            }
        } finally {
            fclose($stream);
        }
    }

    /**
     * Hitung hash SHA-256 untuk verifikasi integritas file.
     */
    public function computeHash(string $filePath): string
    {
        return hash_file('sha256', $filePath);
    }

    /**
     * Generate stored filename yang aman dan tidak mengekspos nama asli.
     */
    public function generateStoredFilename(string $extension): string
    {
        return Str::random(40) . '.' . $extension;
    }
}
