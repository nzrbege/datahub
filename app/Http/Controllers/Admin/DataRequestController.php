<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataFile;
use App\Models\DataRequest;
use App\Services\AuditService;
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

    public function create()
    {
        // Admin hanya bisa request file yang diizinkan untuknya
        $availableFiles = auth()->user()->accessibleFiles()
            ->where('is_active', true)
            ->get();

        return view('admin.requests.create', compact('availableFiles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'data_file_id'      => ['required', 'exists:data_files,id'],
            'alasan_permintaan' => ['required', 'string', 'min:50', 'max:2000'],
            'tujuan_penggunaan' => ['required', 'string', 'min:50', 'max:2000'],
            'dasar_hukum'       => ['required', 'string', 'max:500'],
            'nda_file'          => ['required', 'file', 'mimes:pdf', 'max:5120'],
        ], [
            'alasan_permintaan.min' => 'Alasan permintaan minimal 50 karakter.',
            'tujuan_penggunaan.min' => 'Tujuan penggunaan minimal 50 karakter.',
            'nda_file.required'     => 'Dokumen Perjanjian Kerahasiaan (NDA) wajib dilampirkan.',
            'nda_file.mimes'        => 'Dokumen NDA harus dalam format PDF.',
        ]);

        // Verifikasi admin memang punya akses ke file ini
        $file = DataFile::findOrFail($request->data_file_id);
        if (!auth()->user()->canAccessFile($file)) {
            abort(403, 'Anda tidak memiliki izin untuk mengakses file ini.');
        }

        // Cek tidak ada permintaan pending/approved untuk file yang sama
        $existing = DataRequest::where('user_id', auth()->id())
            ->where('data_file_id', $request->data_file_id)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existing) {
            return back()->withErrors([
                'data_file_id' => 'Anda sudah memiliki permintaan aktif untuk file ini.'
            ]);
        }

        // Simpan NDA
        $ndaFile     = $request->file('nda_file');
        $ndaHash     = hash_file('sha256', $ndaFile->getRealPath());
        $ndaFilename = 'nda_' . auth()->id() . '_' . time() . '.pdf';
        $ndaPath     = $ndaFile->storeAs('nda-documents', $ndaFilename, 'private');

        $dataRequest = DataRequest::create([
            'user_id'           => auth()->id(),
            'data_file_id'      => $request->data_file_id,
            'alasan_permintaan' => $request->alasan_permintaan,
            'tujuan_penggunaan' => $request->tujuan_penggunaan,
            'dasar_hukum'       => $request->dasar_hukum,
            'nda_filename'      => $ndaFile->getClientOriginalName(),
            'nda_path'          => $ndaPath,
            'nda_hash'          => $ndaHash,
            'status'            => 'pending',
            'max_downloads'     => 3,
            'quota_period'      => 'weekly',
        ]);

        $this->audit->log(AuditService::ACTION_REQUEST_CREATE, $dataRequest, [
            'data_file_id' => $request->data_file_id,
            'dasar_hukum'  => $request->dasar_hukum,
        ], $request->dasar_hukum, $request->tujuan_penggunaan);

        return redirect()->route('admin.requests.index')
            ->with('success', 'Permintaan data berhasil diajukan. Menunggu persetujuan Super Admin.');
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
            'tujuan_penggunaan' => ['required', 'string', 'min:50', 'max:2000'],
            'dasar_hukum'       => ['required', 'string', 'max:500'],
            'nda_file'          => ['required', 'file', 'mimes:pdf', 'max:5120'],
        ], [
            'alasan_permintaan.min' => 'Alasan permintaan minimal 50 karakter.',
            'tujuan_penggunaan.min' => 'Tujuan penggunaan minimal 50 karakter.',
            'nda_file.required'     => 'Dokumen Perjanjian Kerahasiaan (NDA) wajib dilampirkan ulang.',
            'nda_file.mimes'        => 'Dokumen NDA harus dalam format PDF.',
        ]);

        $ndaFile = $request->file('nda_file');
        $ndaHash = hash_file('sha256', $ndaFile->getRealPath());
        $ndaFilename = 'nda_' . auth()->id() . '_' . time() . '.pdf';
        $ndaPath = $ndaFile->storeAs('nda-documents', $ndaFilename, 'private');

        if ($dataRequest->nda_path && Storage::disk('private')->exists($dataRequest->nda_path)) {
            Storage::disk('private')->delete($dataRequest->nda_path);
        }

        $dataRequest->update([
            'alasan_permintaan' => $request->alasan_permintaan,
            'tujuan_penggunaan' => $request->tujuan_penggunaan,
            'dasar_hukum'       => $request->dasar_hukum,
            'nda_filename'      => $ndaFile->getClientOriginalName(),
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
            'dasar_hukum' => $request->dasar_hukum,
        ], $request->dasar_hukum, $request->tujuan_penggunaan);

        return redirect()->route('admin.requests.show', $dataRequest)
            ->with('success', 'Revisi permintaan berhasil dikirim. Menunggu review ulang Super Admin.');
    }

    private function authorizeRevision(DataRequest $dataRequest): void
    {
        if ($dataRequest->user_id !== auth()->id()) {
            abort(403);
        }

        if (!$dataRequest->isRejected()) {
            abort(403, 'Hanya permintaan yang ditolak yang bisa direvisi.');
        }
    }
}
