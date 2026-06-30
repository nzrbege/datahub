<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataFile;
use App\Models\DataRequest;
use App\Services\AuditService;
use App\Support\Security\FileResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DataRequestController extends Controller
{
    public function __construct(private AuditService $audit) {}

    public function index(Request $request)
    {
        $query = DataRequest::with(['dataFile'])
            ->where('user_id', auth()->id())
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->paginate(20);

        $requests->getCollection()->each->ensureDownloadToken();

        return view('admin.requests.index', compact('requests'));
    }

    public function create(Request $request)
    {
        // Admin hanya bisa request file yang diizinkan untuknya
        $availableFiles = auth()->user()->accessibleFiles()
            ->where('is_active', true)
            ->get();

        $selectedFile = null;
        if ($request->filled('data_file_id')) {
            $selectedFile = $availableFiles->firstWhere('id', (int) $request->data_file_id);
        }

        return view('admin.requests.create', compact('availableFiles', 'selectedFile'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'data_file_id'      => ['required', 'exists:data_files,id'],
            'alasan_permintaan' => ['required', 'string', 'min:50', 'max:2000'],
            'request_file'      => ['required', 'file', 'mimes:pdf', 'max:5120'],
        ], [
            'alasan_permintaan.min' => 'Alasan dan tujuan minimal 50 karakter.',
            'request_file.required' => 'Dokumen permohonan wajib dilampirkan.',
            'request_file.mimes'    => 'Dokumen permohonan harus dalam format PDF.',
        ]);

        // Verifikasi admin memang punya akses ke file ini
        $file = DataFile::findOrFail($request->data_file_id);
        if (!auth()->user()->canAccessFile($file)) {
            abort(403, 'Anda tidak memiliki izin untuk mengakses file ini.');
        }

        // Cek tidak ada permintaan pending/approved untuk file yang sama
        $existing = DataRequest::where('user_id', auth()->id())
            ->where('data_file_id', $request->data_file_id)
            ->whereIn('status', ['pending', 'returned', 'approved', 'bast_pending', 'bast_approved'])
            ->first();

        if ($existing) {
            return back()->withErrors([
                'data_file_id' => 'Anda sudah memiliki permintaan aktif untuk file ini.'
            ]);
        }

        // Simpan dokumen permohonan
        $ndaFile     = $request->file('request_file');
        $ndaHash     = hash_file('sha256', $ndaFile->getRealPath());
        $ndaFilename = 'permohonan_' . auth()->id() . '_' . time() . '.pdf';
        $ndaPath     = $ndaFile->storeAs('request-documents', $ndaFilename, 'private');

        $dataRequest = DataRequest::create([
            'user_id'           => auth()->id(),
            'data_file_id'      => $request->data_file_id,
            'alasan_permintaan' => $request->alasan_permintaan,
            'tujuan_penggunaan' => $request->alasan_permintaan,
            'dasar_hukum'       => null,
            'nda_filename'      => FileResponse::safeFilename($ndaFile->getClientOriginalName(), 'dokumen-permohonan.pdf'),
            'nda_path'          => $ndaPath,
            'nda_hash'          => $ndaHash,
            'status'            => 'pending',
            'max_downloads'     => 3,
            'quota_period'      => 'weekly',
        ]);

        $this->audit->log(AuditService::ACTION_REQUEST_CREATE, $dataRequest, [
            'data_file_id' => $request->data_file_id,
        ], null, $request->alasan_permintaan);

        return redirect()->route('admin.requests.index')
            ->with('success', 'Permintaan data berhasil diajukan. Menunggu verifikasi Super Admin.');
    }

    public function show(DataRequest $dataRequest)
    {
        // Pastikan admin hanya bisa lihat request miliknya sendiri
        if ($dataRequest->user_id !== auth()->id()) {
            abort(403);
        }

        $dataRequest->load(['dataFile', 'reviewer', 'downloadLogs']);
        $dataRequest->ensureDownloadToken();

        return view('admin.requests.show', compact('dataRequest'));
    }

    public function edit(DataRequest $dataRequest)
    {
        $this->authorizeRevision($dataRequest);

        return view('admin.requests.edit', compact('dataRequest'));
    }

    public function update(Request $request, DataRequest $dataRequest)
    {
        $this->authorizeRevision($dataRequest);

        $request->validate([
            'alasan_permintaan' => ['required', 'string', 'min:50', 'max:2000'],
            'request_file'      => ['required', 'file', 'mimes:pdf', 'max:5120'],
        ], [
            'alasan_permintaan.min' => 'Alasan dan tujuan minimal 50 karakter.',
            'request_file.required' => 'Dokumen permohonan wajib dilampirkan ulang.',
            'request_file.mimes'    => 'Dokumen permohonan harus dalam format PDF.',
        ]);

        $ndaFile = $request->file('request_file');
        $ndaHash = hash_file('sha256', $ndaFile->getRealPath());
        $ndaFilename = 'permohonan_' . auth()->id() . '_' . time() . '.pdf';
        $ndaPath = $ndaFile->storeAs('request-documents', $ndaFilename, 'private');

        if ($dataRequest->nda_path && Storage::disk('private')->exists($dataRequest->nda_path)) {
            Storage::disk('private')->delete($dataRequest->nda_path);
        }

        $dataRequest->update([
            'alasan_permintaan' => $request->alasan_permintaan,
            'tujuan_penggunaan' => $request->alasan_permintaan,
            'dasar_hukum'       => null,
            'nda_filename'      => FileResponse::safeFilename($ndaFile->getClientOriginalName(), 'dokumen-permohonan.pdf'),
            'nda_path'          => $ndaPath,
            'nda_hash'          => $ndaHash,
            'status'            => 'pending',
            'reviewed_by'       => null,
            'reviewed_at'       => null,
            'catatan_reviewer'  => null,
            'download_token'    => null,
            'token_expires_at'  => null,
            'quota_reset_at'    => null,
        ]);

        $this->audit->log(AuditService::ACTION_REQUEST_REVISE, $dataRequest, [
            'data_file_id' => $dataRequest->data_file_id,
        ], null, $request->alasan_permintaan);

        return redirect()->route('admin.requests.show', $dataRequest)
            ->with('success', 'Revisi permintaan berhasil dikirim. Menunggu review ulang Super Admin.');
    }

    public function uploadBast(Request $request, DataRequest $dataRequest)
    {
        if ($dataRequest->user_id !== auth()->id()) {
            abort(403);
        }

        if (!$dataRequest->needsBastUpload()) {
            abort(403, 'Dokumen BAST hanya dapat diunggah setelah permohonan disetujui Super Admin.');
        }

        $request->validate([
            'bast_file' => ['required', 'file', 'mimes:pdf', 'max:5120'],
        ], [
            'bast_file.required' => 'Dokumen BAST wajib dilampirkan.',
            'bast_file.mimes' => 'Dokumen BAST harus dalam format PDF.',
        ]);

        $bastFile = $request->file('bast_file');
        $bastHash = hash_file('sha256', $bastFile->getRealPath());
        $bastFilename = 'bast_' . auth()->id() . '_' . time() . '.pdf';
        $bastPath = $bastFile->storeAs('bast-documents', $bastFilename, 'private');

        if ($dataRequest->bast_path && Storage::disk('private')->exists($dataRequest->bast_path)) {
            Storage::disk('private')->delete($dataRequest->bast_path);
        }

        $dataRequest->update([
            'bast_filename' => FileResponse::safeFilename($bastFile->getClientOriginalName(), 'bast.pdf'),
            'bast_path' => $bastPath,
            'bast_hash' => $bastHash,
            'status' => 'bast_pending',
            'bast_reviewed_by' => null,
            'bast_reviewed_at' => null,
            'catatan_bast' => null,
        ]);

        $this->audit->log(AuditService::ACTION_REQUEST_REVISE, $dataRequest, [
            'data_file_id' => $dataRequest->data_file_id,
            'bast_filename' => $dataRequest->bast_filename,
        ]);

        return redirect()->route('admin.requests.show', $dataRequest)
            ->with('success', 'Dokumen BAST berhasil diunggah. Menunggu verifikasi Super Admin.');
    }

    private function authorizeRevision(DataRequest $dataRequest): void
    {
        if ($dataRequest->user_id !== auth()->id()) {
            abort(403);
        }

        if (!$dataRequest->isReturned()) {
            abort(403, 'Hanya permintaan yang dikembalikan yang bisa direvisi.');
        }
    }
}
