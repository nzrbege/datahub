<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\DataFile;
use App\Models\DataFilePermission;
use App\Models\User;
use App\Services\AuditService;
use App\Services\FileStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DataFileController extends Controller
{
    public function __construct(
        private AuditService $audit,
        private FileStorageService $fileStorage
    ) {}

    public function index()
    {
        $files = DataFile::with('uploader')
            ->withCount('allowedUsers')
            ->latest()
            ->paginate(20);

        return view('superadmin.files.index', compact('files'));
    }

    public function create()
    {
        $admins = User::role('admin')->where('is_active', true)->get();
        return view('superadmin.files.create', compact('admins'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul'       => ['required', 'string', 'max:255'],
            'deskripsi'   => ['nullable', 'string', 'max:2000'],
            'file'        => ['required', 'file', 'mimes:xlsx,csv,zip', 'max:51200'],
            'kategori'    => ['required', Rule::in(['DATASET_KELUARGA', 'DATASET_ANGGOTA_KELUARGA'])],
            'tahun_data'  => ['nullable', 'regex:/^(20[0-9]{2}|20[0-9]{2}-(0[1-9]|1[0-2]))$/'],
            'admin_ids'   => ['array'],
            'admin_ids.*' => ['exists:users,id'],
        ]);

        DB::beginTransaction();
        try {
            $file         = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $extension    = $file->getClientOriginalExtension();
            $hash         = $this->fileStorage->computeHash($file->getRealPath());
            $storedName   = $this->fileStorage->generateStoredFilename($extension);
            $storedPath   = 'data-files/' . $storedName;

            // Enkripsi secara streaming lalu simpan ke storage private.
            $this->fileStorage->encryptAndStore($file->getRealPath(), $storedPath);

            $dataFile = DataFile::create([
                'judul'             => $request->judul,
                'deskripsi'         => $request->deskripsi,
                'original_filename' => $originalName,
                'stored_filename'   => $storedName,
                'file_path'         => $storedPath,
                'file_type'         => $extension,
                'file_size'         => $file->getSize(),
                'file_hash'         => $hash,
                'is_encrypted'      => true,
                'kategori'          => $request->kategori,
                'wilayah'           => null,
                'tahun_data'        => $request->tahun_data,
                'uploaded_by'       => auth()->id(),
            ]);

            // Set permission untuk admin yang dipilih
            if ($request->has('admin_ids')) {
                foreach ($request->admin_ids as $adminId) {
                    DataFilePermission::create([
                        'data_file_id'      => $dataFile->id,
                        'user_id'           => $adminId,
                        'granted_at'        => now(),
                        'granted_by'        => auth()->id(),
                        'can_download'      => true,
                        'can_view_metadata' => true,
                    ]);
                }
            }

            $this->audit->log(AuditService::ACTION_FILE_UPLOAD, $dataFile, [
                'original_filename' => $originalName,
                'file_size'         => $file->getSize(),
                'allowed_admins'    => $request->admin_ids ?? [],
            ]);

            DB::commit();
            return redirect()->route('superadmin.files.index')
                ->with('success', 'File berhasil diunggah dan dienkripsi.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['file' => 'Gagal mengunggah file: ' . $e->getMessage()]);
        }
    }

    public function show(DataFile $dataFile)
    {
        $dataFile->load(['uploader', 'allowedUsers', 'dataRequests.user']);
        $allAdmins   = User::role('admin')->where('is_active', true)->get();
        $allowedIds  = $dataFile->allowedUsers->pluck('id')->toArray();

        $this->audit->log(AuditService::ACTION_FILE_VIEW, $dataFile);

        return view('superadmin.files.show', compact('dataFile', 'allAdmins', 'allowedIds'));
    }

    public function download(DataFile $dataFile): StreamedResponse
    {
        try {
            $this->audit->logDownload(
                auth()->id(),
                $dataFile->id,
                null,
                false,
                'success',
                'Unduhan langsung oleh Super Admin'
            );

            $this->audit->log(AuditService::ACTION_FILE_DOWNLOAD, $dataFile, [
                'downloaded_by_role' => 'super_admin',
                'source'             => 'superadmin.files.show',
            ]);

            return response()->streamDownload(function () use ($dataFile) {
                $this->fileStorage->streamFromStorage($dataFile->file_path, $dataFile->is_encrypted, function (string $chunk) {
                    echo $chunk;
                });
            }, $dataFile->original_filename, [
                'Content-Type'           => $this->getMimeType($dataFile->file_type),
                'Content-Disposition'    => 'attachment; filename="' . $dataFile->original_filename . '"',
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control'          => 'no-store, no-cache, must-revalidate',
                'Pragma'                 => 'no-cache',
            ]);
        } catch (\Exception $e) {
            Log::error('Super admin dataset download failed', [
                'user_id'      => auth()->id(),
                'data_file_id' => $dataFile->id,
                'error'        => $e->getMessage(),
            ]);

            $this->audit->logDownload(
                auth()->id(),
                $dataFile->id,
                null,
                false,
                'failed',
                'Error: ' . $e->getMessage()
            );

            abort(500, 'Terjadi kesalahan saat mengunduh file.');
        }
    }

    public function updatePermissions(Request $request, DataFile $dataFile)
    {
        $request->validate([
            'admin_ids'   => ['array'],
            'admin_ids.*' => ['exists:users,id'],
        ]);

        DB::beginTransaction();
        try {
            // Hapus semua permission lama
            DataFilePermission::where('data_file_id', $dataFile->id)->delete();

            // Tambah permission baru
            foreach ($request->admin_ids ?? [] as $adminId) {
                DataFilePermission::create([
                    'data_file_id'      => $dataFile->id,
                    'user_id'           => $adminId,
                    'granted_at'        => now(),
                    'granted_by'        => auth()->id(),
                    'can_download'      => true,
                    'can_view_metadata' => true,
                ]);
            }

            $this->audit->log(AuditService::ACTION_PERMISSION_GRANT, $dataFile, [
                'new_allowed_admins' => $request->admin_ids ?? [],
            ]);

            DB::commit();
            return back()->with('success', 'Izin akses berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal memperbarui izin: ' . $e->getMessage()]);
        }
    }

    public function destroy(DataFile $dataFile)
    {
        $this->audit->log(AuditService::ACTION_FILE_DELETE, $dataFile, [
            'original_filename' => $dataFile->original_filename,
        ]);
        $dataFile->delete(); // soft delete
        return redirect()->route('superadmin.files.index')
            ->with('success', 'File berhasil dihapus.');
    }

    private function getMimeType(string $extension): string
    {
        return match (strtolower($extension)) {
            'xlsx'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'csv'   => 'text/csv',
            'zip'   => 'application/zip',
            default => 'application/octet-stream',
        };
    }
}
