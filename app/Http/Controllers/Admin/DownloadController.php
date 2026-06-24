<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataRequest;
use App\Models\DownloadPicContact;
use App\Services\AuditService;
use App\Services\FileStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DownloadController extends Controller
{
    public function __construct(
        private AuditService $audit,
        private FileStorageService $fileStorage
    ) {}

    /**
     * Tampilkan halaman download + captcha
     */
    public function showDownloadPage(DataRequest $dataRequest)
    {
        if ($dataRequest->user_id !== auth()->id()) {
            abort(403);
        }

        $dataRequest->ensureDownloadToken();

        if (!$dataRequest->canDownload()) {
            return back()->withErrors(['error' => 'Permintaan ini tidak dapat diunduh saat ini.']);
        }

        $downloadPicContact = DownloadPicContact::current();

        return view('admin.download.show', compact('dataRequest', 'downloadPicContact'));
    }

    /**
     * Proses download setelah captcha diverifikasi
     */
    public function download(Request $request, DataRequest $dataRequest)
    {
        if ($dataRequest->user_id !== auth()->id()) {
            abort(403);
        }

        $dataRequest->ensureDownloadToken();

        // Validasi captcha
        $request->validate([
            'captcha' => ['required', 'captcha'],
        ], [
            'captcha.captcha' => 'Kode captcha tidak valid. Silakan coba lagi.',
        ]);

        // Verifikasi request masih valid
        if (!$dataRequest->canDownload()) {
            $this->audit->logDownload(
                auth()->id(),
                $dataRequest->data_file_id,
                $dataRequest->id,
                true,
                'blocked',
                'Batas download periode berjalan tercapai'
            );
            return back()->withErrors(['error' => 'Batas download periode ini telah tercapai. Hubungi Super Admin jika kuota perlu direset.']);
        }

        $dataFile = $dataRequest->dataFile;

        try {
            $this->fileStorage->assertReadable($dataFile->file_path, $dataFile->is_encrypted);

            // Tambah download count
            $dataRequest->increment('download_count');

            $this->audit->logDownload(
                auth()->id(),
                $dataFile->id,
                $dataRequest->id,
                true,
                'success'
            );

            $this->audit->log(AuditService::ACTION_FILE_DOWNLOAD, $dataFile, [
                'request_id'            => $dataRequest->id,
                'download_count'        => $dataRequest->download_count,
                'quota_download_count'  => $dataRequest->quotaDownloadCount(),
                'quota_limit'           => $dataRequest->max_downloads,
                'quota_period'          => $dataRequest->quota_period,
            ], $dataRequest->dasar_hukum, $dataRequest->tujuan_penggunaan);

            // Stream file ke browser tanpa menyimpan di disk
            return response()->streamDownload(function () use ($dataFile) {
                $this->fileStorage->streamFromStorage($dataFile->file_path, $dataFile->is_encrypted, function (string $chunk) {
                    echo $chunk;
                });
            }, $dataFile->original_filename, [
                'Content-Type'        => $this->getMimeType($dataFile->file_type),
                'Content-Disposition' => 'attachment; filename="' . $dataFile->original_filename . '"',
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control'       => 'no-store, no-cache, must-revalidate',
                'Pragma'              => 'no-cache',
            ]);
        } catch (\Exception $e) {
            Log::error('Download failed', [
                'user_id'    => auth()->id(),
                'request_id' => $dataRequest->id,
                'error'      => $e->getMessage(),
            ]);

            $this->audit->logDownload(
                auth()->id(),
                $dataRequest->data_file_id,
                $dataRequest->id,
                true,
                'failed',
                'Error: ' . $e->getMessage()
            );

            return back()->withErrors(['error' => 'Terjadi kesalahan saat mengunduh file. Silakan hubungi Super Admin.']);
        }
    }

    private function getMimeType(string $extension): string
    {
        return match(strtolower($extension)) {
            'xlsx'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'csv'   => 'text/csv',
            'zip'   => 'application/zip',
            default => 'application/octet-stream',
        };
    }
}
