<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
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
        $query = DataRequest::with(['user', 'dataFile', 'reviewer'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->paginate(20);
        return view('superadmin.requests.index', compact('requests'));
    }

    public function show(DataRequest $dataRequest)
    {
        $dataRequest->load(['user', 'dataFile', 'reviewer', 'bastReviewer', 'downloadLogs.user']);
        return view('superadmin.requests.show', compact('dataRequest'));
    }

    public function nda(Request $request, DataRequest $dataRequest)
    {
        if (!$dataRequest->nda_path || !Storage::disk('private')->exists($dataRequest->nda_path)) {
            abort(404, 'Dokumen permohonan tidak ditemukan.');
        }

        $mode = $request->query('mode') === 'download' ? 'download' : 'view';

        $this->audit->log(
            $mode === 'download' ? AuditService::ACTION_NDA_DOWNLOAD : AuditService::ACTION_NDA_VIEW,
            $dataRequest,
            [
                'request_filename' => $dataRequest->nda_filename,
                'request_path' => $dataRequest->nda_path,
            ]
        );

        $path = Storage::disk('private')->path($dataRequest->nda_path);
        $filename = FileResponse::safeFilename($dataRequest->nda_filename, 'dokumen-permohonan.pdf');

        if ($mode === 'download') {
            return response()->download($path, $filename, ['Content-Type' => 'application/pdf']);
        }

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => FileResponse::inlineDisposition($filename, 'dokumen-permohonan.pdf'),
        ]);
    }

    public function bast(Request $request, DataRequest $dataRequest)
    {
        if (!$dataRequest->bast_path || !Storage::disk('private')->exists($dataRequest->bast_path)) {
            abort(404, 'Dokumen BAST tidak ditemukan.');
        }

        $mode = $request->query('mode') === 'download' ? 'download' : 'view';
        $path = Storage::disk('private')->path($dataRequest->bast_path);
        $filename = FileResponse::safeFilename($dataRequest->bast_filename, 'bast.pdf');

        if ($mode === 'download') {
            return response()->download($path, $filename, ['Content-Type' => 'application/pdf']);
        }

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => FileResponse::inlineDisposition($filename, 'bast.pdf'),
        ]);
    }

    public function approve(Request $request, DataRequest $dataRequest)
    {
        if (!$dataRequest->isPending()) {
            return back()->withErrors(['error' => 'Hanya permintaan dengan status pending yang bisa disetujui.']);
        }

        $request->validate([
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        $dataRequest->update([
            'status'           => 'approved',
            'reviewed_by'      => auth()->id(),
            'reviewed_at'      => now(),
            'catatan_reviewer' => $request->catatan,
            'download_token'   => null,
            'token_expires_at' => null,
        ]);

        $this->audit->log(AuditService::ACTION_REQUEST_APPROVE, $dataRequest, [
            'catatan' => $request->catatan,
        ]);

        return back()->with('success', 'Permohonan disetujui. Admin OPD dapat mengunggah dokumen BAST.');
    }

    public function returnRequest(Request $request, DataRequest $dataRequest)
    {
        if (!$dataRequest->isPending()) {
            return back()->withErrors(['error' => 'Hanya permintaan dengan status pending yang bisa dikembalikan.']);
        }

        $request->validate([
            'catatan' => ['required', 'string', 'max:1000'],
        ]);

        $dataRequest->update([
            'status'           => 'returned',
            'reviewed_by'      => auth()->id(),
            'reviewed_at'      => now(),
            'catatan_reviewer' => $request->catatan,
        ]);

        $this->audit->log(AuditService::ACTION_REQUEST_REJECT, $dataRequest, [
            'catatan' => $request->catatan,
            'decision' => 'returned',
        ]);

        return back()->with('success', 'Permohonan dikembalikan ke Admin OPD untuk diperbaiki.');
    }

    public function reject(Request $request, DataRequest $dataRequest)
    {
        if (!$dataRequest->isPending()) {
            return back()->withErrors(['error' => 'Hanya permintaan dengan status pending yang bisa ditolak.']);
        }

        $request->validate([
            'catatan' => ['required', 'string', 'max:1000'],
        ]);

        $dataRequest->update([
            'status'           => 'rejected',
            'reviewed_by'      => auth()->id(),
            'reviewed_at'      => now(),
            'catatan_reviewer' => $request->catatan,
        ]);

        $this->audit->log(AuditService::ACTION_REQUEST_REJECT, $dataRequest, [
            'catatan' => $request->catatan,
        ]);

        return back()->with('success', 'Permintaan telah ditolak.');
    }

    public function approveBast(Request $request, DataRequest $dataRequest)
    {
        if (!$dataRequest->isBastPending()) {
            return back()->withErrors(['error' => 'Hanya BAST yang menunggu verifikasi yang bisa disetujui.']);
        }

        $request->validate([
            'catatan' => ['nullable', 'string', 'max:1000'],
            'max_downloads' => ['required', 'integer', 'min:1', 'max:999'],
            'quota_period' => ['required', 'in:daily,weekly,monthly,lifetime'],
        ]);

        $dataRequest->update([
            'status' => 'bast_approved',
            'bast_reviewed_by' => auth()->id(),
            'bast_reviewed_at' => now(),
            'catatan_bast' => $request->catatan,
            'max_downloads' => $request->max_downloads,
            'quota_period' => $request->quota_period,
            'quota_reset_at' => null,
        ]);
        $dataRequest->generateDownloadToken();

        $this->audit->log(AuditService::ACTION_REQUEST_APPROVE, $dataRequest, [
            'catatan' => $request->catatan,
            'stage' => 'bast',
            'max_downloads' => $request->max_downloads,
            'quota_period' => $request->quota_period,
        ]);

        return back()->with('success', 'Dokumen BAST disetujui. Admin OPD sudah dapat mengunduh data.');
    }

    public function returnBast(Request $request, DataRequest $dataRequest)
    {
        if (!$dataRequest->isBastPending()) {
            return back()->withErrors(['error' => 'Hanya BAST yang menunggu verifikasi yang bisa dikembalikan.']);
        }

        $request->validate([
            'catatan' => ['required', 'string', 'max:1000'],
        ]);

        $dataRequest->update([
            'status' => 'approved',
            'bast_reviewed_by' => auth()->id(),
            'bast_reviewed_at' => now(),
            'catatan_bast' => $request->catatan,
            'download_token' => null,
            'token_expires_at' => null,
        ]);

        $this->audit->log(AuditService::ACTION_REQUEST_REJECT, $dataRequest, [
            'catatan' => $request->catatan,
            'stage' => 'bast',
            'decision' => 'returned',
        ]);

        return back()->with('success', 'Dokumen BAST dikembalikan ke Admin OPD untuk diunggah ulang.');
    }

    public function revoke(Request $request, DataRequest $dataRequest)
    {
        if (!$dataRequest->isApproved()) {
            return back()->withErrors(['error' => 'Hanya permintaan yang sudah disetujui yang bisa dicabut.']);
        }

        $request->validate([
            'catatan' => ['required', 'string', 'max:1000'],
        ]);

        $dataRequest->update([
            'status'           => 'revoked',
            'reviewed_by'      => auth()->id(),
            'reviewed_at'      => now(),
            'catatan_reviewer' => $request->catatan,
            'download_token'   => null,
            'token_expires_at' => null,
        ]);

        $this->audit->log(AuditService::ACTION_REQUEST_REVOKE, $dataRequest, [
            'catatan' => $request->catatan,
        ]);

        return back()->with('success', 'Persetujuan berhasil dicabut.');
    }

    public function updateQuota(Request $request, DataRequest $dataRequest)
    {
        if (!$dataRequest->isApproved()) {
            return back()->withErrors(['error' => 'Kuota hanya bisa diubah untuk permintaan yang sudah disetujui.']);
        }

        $request->validate([
            'max_downloads' => ['required', 'integer', 'min:1', 'max:999'],
            'quota_period' => ['required', 'in:daily,weekly,monthly,lifetime'],
        ]);

        $dataRequest->update([
            'max_downloads' => $request->max_downloads,
            'quota_period' => $request->quota_period,
            'download_token' => null,
            'token_expires_at' => null,
        ]);
        $dataRequest->ensureDownloadToken();

        $this->audit->log(AuditService::ACTION_QUOTA_UPDATE, $dataRequest, [
            'max_downloads' => $request->max_downloads,
            'quota_period' => $request->quota_period,
        ]);

        return back()->with('success', 'Pengaturan kuota download berhasil diperbarui.');
    }

    public function resetQuota(DataRequest $dataRequest)
    {
        if (!$dataRequest->isApproved()) {
            return back()->withErrors(['error' => 'Kuota hanya bisa direset untuk permintaan yang sudah disetujui.']);
        }

        $dataRequest->update([
            'quota_reset_at' => now(),
            'download_token' => null,
            'token_expires_at' => null,
        ]);
        $dataRequest->ensureDownloadToken();

        $this->audit->log(AuditService::ACTION_QUOTA_RESET, $dataRequest, [
            'quota_reset_at' => $dataRequest->quota_reset_at?->toIso8601String(),
            'max_downloads' => $dataRequest->max_downloads,
            'quota_period' => $dataRequest->quota_period,
        ]);

        return back()->with('success', 'Kuota download berhasil direset.');
    }
}
