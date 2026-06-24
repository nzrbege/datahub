<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class FileStorageService
{
    private const MAGIC = "DTSENC3\0";
    private const CHUNK_SIZE = 1048576; // 1 MB

    /**
     * Enkripsi file secara streaming dan simpan atomik ke storage private.
     */
    public function encryptAndStore(string $sourcePath, string $diskPath): bool
    {
        $this->ensureSodiumAvailable();

        $targetPath = Storage::disk('private')->path($diskPath);
        $targetDirectory = dirname($targetPath);

        if (!is_dir($targetDirectory) && !mkdir($targetDirectory, 0755, true) && !is_dir($targetDirectory)) {
            throw new RuntimeException("Tidak bisa membuat direktori penyimpanan: {$targetDirectory}");
        }

        $temporaryPath = $targetPath . '.tmp.' . bin2hex(random_bytes(8));
        $input = fopen($sourcePath, 'rb');
        $output = fopen($temporaryPath, 'wb');

        if ($input === false || $output === false) {
            if (is_resource($input)) {
                fclose($input);
            }
            if (is_resource($output)) {
                fclose($output);
            }
            @unlink($temporaryPath);
            throw new RuntimeException('Tidak bisa membuka file untuk proses enkripsi.');
        }

        $key = $this->getKey();
        [$state, $header] = sodium_crypto_secretstream_xchacha20poly1305_init_push($key);
        $stored = false;

        try {
            $this->writeAll($output, self::MAGIC . $header);
            $chunk = fread($input, self::CHUNK_SIZE);

            if ($chunk === false) {
                throw new RuntimeException('Gagal membaca file sumber.');
            }

            while (true) {
                $nextChunk = fread($input, self::CHUNK_SIZE);

                if ($nextChunk === false) {
                    throw new RuntimeException('Gagal membaca file sumber.');
                }

                $isFinal = $nextChunk === '' && feof($input);
                $tag = $isFinal
                    ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_TAG_FINAL
                    : SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_TAG_MESSAGE;
                $encrypted = sodium_crypto_secretstream_xchacha20poly1305_push($state, $chunk, '', $tag);

                $this->writeAll($output, pack('N', strlen($encrypted)));
                $this->writeAll($output, $encrypted);

                if ($isFinal) {
                    break;
                }

                $chunk = $nextChunk;
            }

            fflush($output);
            fclose($input);
            fclose($output);
            $input = null;
            $output = null;

            if (!rename($temporaryPath, $targetPath)) {
                throw new RuntimeException('Gagal memindahkan file terenkripsi ke storage.');
            }

            $stored = true;
            return true;
        } finally {
            if (is_resource($input)) {
                fclose($input);
            }
            if (is_resource($output)) {
                fclose($output);
            }
            if (!$stored) {
                @unlink($temporaryPath);
            }
            sodium_memzero($key);
        }
    }

    /**
     * Stream file; file terenkripsi diverifikasi penuh sebelum didekripsi ke output.
     */
    public function streamFromStorage(string $diskPath, bool $encrypted, callable $write): void
    {
        $stream = Storage::disk('private')->readStream($diskPath);

        if ($stream === false) {
            throw new RuntimeException('File tidak ditemukan di storage.');
        }

        if (!$encrypted) {
            try {
                $this->streamRaw($stream, $write);
            } finally {
                fclose($stream);
            }
            return;
        }

        $this->ensureSodiumAvailable();
        $key = $this->getKey();

        try {
            // Pass pertama memastikan seluruh file autentik dan tidak terpotong.
            $this->processEncryptedStream($stream, $key);

            if (rewind($stream) === false) {
                throw new RuntimeException('Gagal membaca ulang file terenkripsi.');
            }

            // Pass kedua baru mengirim plaintext ke browser.
            $this->processEncryptedStream($stream, $key, $write);
        } finally {
            fclose($stream);
            sodium_memzero($key);
        }
    }

    public function assertReadable(string $diskPath, bool $encrypted): void
    {
        $stream = Storage::disk('private')->readStream($diskPath);

        if ($stream === false) {
            throw new RuntimeException('File tidak ditemukan di storage.');
        }

        if (!$encrypted) {
            fclose($stream);
            return;
        }

        $this->ensureSodiumAvailable();
        $key = $this->getKey();

        try {
            $this->processEncryptedStream($stream, $key);
        } finally {
            fclose($stream);
            sodium_memzero($key);
        }
    }

    public function computeHash(string $filePath): string
    {
        return hash_file('sha256', $filePath);
    }

    public function generateStoredFilename(string $extension): string
    {
        return Str::random(40) . '.' . $extension . '.enc';
    }

    private function processEncryptedStream($stream, string $key, ?callable $write = null): void
    {
        $magic = $this->readExact($stream, strlen(self::MAGIC));

        if ($magic !== self::MAGIC) {
            throw new RuntimeException('Format file terenkripsi tidak dikenali.');
        }

        $header = $this->readExact(
            $stream,
            SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES
        );
        $state = sodium_crypto_secretstream_xchacha20poly1305_init_pull($header, $key);
        $finalSeen = false;

        while (($lengthBytes = $this->readExact($stream, 4, true)) !== null) {
            $length = unpack('Nlength', $lengthBytes)['length'];
            $maximumLength = self::CHUNK_SIZE + SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_ABYTES;

            if ($length < SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_ABYTES || $length > $maximumLength) {
                throw new RuntimeException('Ukuran chunk file terenkripsi tidak valid.');
            }

            $encrypted = $this->readExact($stream, $length);
            $result = sodium_crypto_secretstream_xchacha20poly1305_pull($state, $encrypted);

            if ($result === false) {
                throw new RuntimeException('Integritas file gagal: file telah berubah atau kunci tidak sesuai.');
            }

            [$plain, $tag] = $result;

            if ($write !== null && $plain !== '') {
                $write($plain);
            }

            if ($tag === SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_TAG_FINAL) {
                if ($this->readExact($stream, 1, true) !== null) {
                    throw new RuntimeException('Data tambahan ditemukan setelah akhir file terenkripsi.');
                }
                $finalSeen = true;
                break;
            }

            if ($tag !== SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_TAG_MESSAGE) {
                throw new RuntimeException('Tag chunk file terenkripsi tidak valid.');
            }
        }

        if (!$finalSeen) {
            throw new RuntimeException('File terenkripsi tidak lengkap atau terpotong.');
        }
    }

    private function streamRaw($stream, callable $write): void
    {
        while (!feof($stream)) {
            $chunk = fread($stream, self::CHUNK_SIZE);

            if ($chunk === false) {
                throw new RuntimeException('Gagal membaca file dari storage.');
            }

            if ($chunk !== '') {
                $write($chunk);
            }
        }
    }

    private function getKey(): string
    {
        $configuredKey = (string) (config('app.file_encryption_key') ?: config('app.key'));

        if ($configuredKey === '') {
            throw new RuntimeException('FILE_ENCRYPTION_KEY atau APP_KEY belum dikonfigurasi.');
        }

        if (str_starts_with($configuredKey, 'base64:')) {
            $decoded = base64_decode(substr($configuredKey, 7), true);
            if ($decoded !== false) {
                $configuredKey = $decoded;
            }
        }

        return sodium_crypto_generichash(
            "dataset-file-encryption\0" . $configuredKey,
            '',
            SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_KEYBYTES
        );
    }

    private function ensureSodiumAvailable(): void
    {
        if (!function_exists('sodium_crypto_secretstream_xchacha20poly1305_init_push')) {
            throw new RuntimeException('Extension PHP sodium diperlukan untuk enkripsi file.');
        }
    }

    private function writeAll($stream, string $contents): void
    {
        $offset = 0;
        $length = strlen($contents);

        while ($offset < $length) {
            $written = fwrite($stream, substr($contents, $offset));

            if ($written === false || $written === 0) {
                throw new RuntimeException('Gagal menulis file terenkripsi.');
            }

            $offset += $written;
        }
    }

    private function readExact($stream, int $length, bool $allowEof = false): ?string
    {
        $contents = '';

        while (strlen($contents) < $length) {
            $chunk = fread($stream, $length - strlen($contents));

            if ($chunk === false) {
                throw new RuntimeException('Gagal membaca file terenkripsi.');
            }

            if ($chunk === '') {
                if ($allowEof && $contents === '' && feof($stream)) {
                    return null;
                }
                throw new RuntimeException('File terenkripsi tidak lengkap atau terpotong.');
            }

            $contents .= $chunk;
        }

        return $contents;
    }
}
