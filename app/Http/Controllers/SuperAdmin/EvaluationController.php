<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\DataUtilizationEvaluation;
use App\Services\AuditService;
use App\Support\Security\FileResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EvaluationController extends Controller
{
    public function __construct(private AuditService $audit) {}

    public function index(Request $request)
    {
        $query = DataUtilizationEvaluation::with(['user', 'dataFile', 'dataRequest'])
            ->latest('submitted_at');

        if ($request->filled('q')) {
            $term = $request->q;
            $query->where(function ($inner) use ($term) {
                $inner->whereHas('user', function ($userQuery) use ($term) {
                    $userQuery->where('name', 'like', "%{$term}%")
                        ->orWhere('instansi', 'like', "%{$term}%");
                })->orWhereHas('dataFile', function ($fileQuery) use ($term) {
                    $fileQuery->where('judul', 'like', "%{$term}%");
                });
            });
        }

        $evaluations = $query->paginate(20)->withQueryString();

        return view('superadmin.evaluations.index', compact('evaluations'));
    }

    public function show(DataUtilizationEvaluation $evaluation)
    {
        if (!Storage::disk('private')->exists($evaluation->report_path)) {
            abort(404, 'Dokumen evaluasi tidak ditemukan.');
        }

        $this->audit->log('utilization_evaluation_view', $evaluation, [
            'data_request_id' => $evaluation->data_request_id,
            'report_filename' => $evaluation->report_filename,
        ]);

        return response()->file(Storage::disk('private')->path($evaluation->report_path), [
            'Content-Disposition' => FileResponse::inlineDisposition($evaluation->report_filename, 'evaluasi-pemanfaatan'),
        ]);
    }

    public function download(DataUtilizationEvaluation $evaluation)
    {
        if (!Storage::disk('private')->exists($evaluation->report_path)) {
            abort(404, 'Dokumen evaluasi tidak ditemukan.');
        }

        $this->audit->log('utilization_evaluation_download', $evaluation, [
            'data_request_id' => $evaluation->data_request_id,
            'report_filename' => $evaluation->report_filename,
        ]);

        return response()->download(
            Storage::disk('private')->path($evaluation->report_path),
            FileResponse::safeFilename($evaluation->report_filename, 'evaluasi-pemanfaatan')
        );
    }
}
