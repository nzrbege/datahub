<?php

namespace App\Http\Controllers;

use App\Models\DataRequest;
use App\Support\Security\FileResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DataRequestMonitoringController extends Controller
{
    private const STATUS_ORDER = [
        'pending',
        'returned',
        'approved',
        'bast_pending',
        'bast_approved',
        'bast_rejected',
        'rejected',
        'revoked',
    ];

    public function index(Request $request)
    {
        $this->authorizeMonitoring();

        $baseQuery = DataRequest::query();

        $statusCounts = DataRequest::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        if ($request->filled('status')) {
            $baseQuery->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $term = $request->q;
            $baseQuery->where(function ($query) use ($term) {
                $query->whereHas('user', function ($userQuery) use ($term) {
                    $userQuery->where('name', 'like', "%{$term}%")
                        ->orWhere('instansi', 'like', "%{$term}%");
                })->orWhereHas('dataFile', function ($fileQuery) use ($term) {
                    $fileQuery->where('judul', 'like', "%{$term}%")
                        ->orWhere('kategori', 'like', "%{$term}%");
                });
            });
        }

        $requests = $baseQuery
            ->with(['user', 'dataFile', 'reviewer', 'bastReviewer', 'utilizationEvaluation'])
            ->withCount([
                'downloadLogs as successful_downloads_count' => fn ($query) => $query->where('status', 'success'),
            ])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('monitoring.requests.index', [
            'requests' => $requests,
            'statusCounts' => $statusCounts,
            'statuses' => self::STATUS_ORDER,
        ]);
    }

    public function document(Request $request, DataRequest $dataRequest, string $type)
    {
        $this->authorizeMonitoring();

        [$path, $filename] = match ($type) {
            'permohonan' => [$dataRequest->nda_path, $dataRequest->nda_filename ?: 'dokumen-permohonan.pdf'],
            'bast' => [$dataRequest->bast_path, $dataRequest->bast_filename ?: 'bast.pdf'],
            'evaluasi' => [
                $dataRequest->utilizationEvaluation?->report_path,
                $dataRequest->utilizationEvaluation?->report_filename ?: 'evaluasi-pemanfaatan',
            ],
            default => abort(404),
        };

        if (!$path || !Storage::disk('private')->exists($path)) {
            abort(404, 'Dokumen tidak ditemukan.');
        }

        $absolutePath = Storage::disk('private')->path($path);

        if ($request->query('mode') === 'download') {
            return response()->download($absolutePath, FileResponse::safeFilename($filename));
        }

        return response()->file($absolutePath, [
            'Content-Disposition' => FileResponse::inlineDisposition($filename),
        ]);
    }

    private function authorizeMonitoring(): void
    {
        if (!auth()->user()?->canAccessRequestMonitoring()) {
            abort(403, 'Anda tidak memiliki izin untuk mengakses monitoring permohonan data.');
        }
    }
}
