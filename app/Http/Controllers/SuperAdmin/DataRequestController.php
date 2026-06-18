<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\DataRequest;
use App\Services\AuditService;
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
        $dataRequest->load(['user', 'dataFile', 'reviewer', 'downloadLogs.user']);
        return view('superadmin.requests.show', compact('dataRequest'));
    }

    public function nda(Request $request, DataRequest $dataRequest)
    {
        if (!$dataRequest->nda_path || !Storage::disk('private')->exists($dataRequest->nda_path)) {
            abort(404, 'Dokumen NDA tidak ditemukan.');
        }

        $mode = $request->query('mode') === 'download' ? 'download' : 'view';

        $this->audit->log(
            $mode === 'download' ? AuditService::ACTION_NDA_DOWNLOAD : AuditService::ACTION_NDA_VIEW,
            $dataRequest,
            [
                'nda_filename' => $dataRequest->nda_filename,
                'nda_path' => $dataRequest->nda_path,
            ]
        );

        $path = Storage::disk('private')->path($dataRequest->nda_path);
        $filename = $dataRequest->nda_filename ?: 'nda.pdf';

        if ($mode === 'download') {
            return response()->download($path, $filename, ['Content-Type' => 'application/pdf']);
        }

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . addslashes($filename) . '"',
        ]);
    }

    public function approve(Request $request, DataRequest $dataRequest)
    {
        if (!$dataRequest->isPending()) {
            return back()->withErrors(['error' => 'Hanya permintaan dengan status pending yang bisa disetujui.']);
        }

        $request->validate([
            'catatan' => ['nullable', 'string', 'max:1000'],
            'max_downloads' => ['required', 'integer', 'min:1', 'max:999'],
            'quota_period' => ['required', 'in:daily,weekly,monthly,lifetime'],
        ]);

        $dataRequest->update([
            'status'           => 'approved',
            'reviewed_by'      => auth()->id(),
            'reviewed_at'      => now(),
            'catatan_reviewer' => $request->catatan,
            'max_downloads'     => $request->max_downloads,
            'quota_period'      => $request->quota_period,
            'quota_reset_at'    => null,
        ]);
        $dataRequest->generateDownloadToken();

        $this->audit->log(AuditService::ACTION_REQUEST_APPROVE, $dataRequest, [
            'catatan' => $request->catatan,
            'max_downloads' => $request->max_downloads,
            'quota_period' => $request->quota_period,
        ]);

        // Kirim notifikasi ke admin (bisa diperluas dengan notification)
        return back()->with('success', 'Permintaan telah disetujui. Admin dapat mengunduh setelah memasukkan captcha.');
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
