<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataRequest;
use App\Models\DataUtilizationEvaluation;
use App\Services\AuditService;
use App\Support\Security\FileResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EvaluationController extends Controller
{
    public function __construct(private AuditService $audit) {}

    public function index()
    {
        $requests = DataRequest::with([
                'dataFile',
                'utilizationEvaluation',
                'downloadLogs' => fn ($query) => $query->where('status', 'success')->latest('downloaded_at'),
            ])
            ->where('user_id', auth()->id())
            ->where('status', 'bast_approved')
            ->whereHas('downloadLogs', fn ($query) => $query->where('status', 'success'))
            ->latest('updated_at')
            ->paginate(20);

        return view('admin.evaluations.index', compact('requests'));
    }

    public function store(Request $request, DataRequest $dataRequest)
    {
        if ($dataRequest->user_id !== auth()->id()) {
            abort(403);
        }

        if (!$dataRequest->isApproved() || !$dataRequest->downloadLogs()->where('status', 'success')->exists()) {
            abort(403, 'Evaluasi hanya dapat diunggah untuk data yang sudah disetujui dan pernah diunduh.');
        }

        $request->validate([
            'report_file' => ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx', 'max:10240'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ], [
            'report_file.required' => 'Dokumen evaluasi pemanfaatan wajib dilampirkan.',
            'report_file.mimes' => 'Dokumen evaluasi harus berformat PDF, DOC, DOCX, XLS, atau XLSX.',
            'report_file.max' => 'Ukuran dokumen evaluasi maksimal 10MB.',
        ]);

        $file = $request->file('report_file');
        $hash = hash_file('sha256', $file->getRealPath());
        $storedName = 'evaluasi_' . auth()->id() . '_' . $dataRequest->id . '_' . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('utilization-evaluations', $storedName, 'private');

        $evaluation = $dataRequest->utilizationEvaluation;
        if ($evaluation && Storage::disk('private')->exists($evaluation->report_path)) {
            Storage::disk('private')->delete($evaluation->report_path);
        }

        $evaluation = DataUtilizationEvaluation::updateOrCreate(
            ['data_request_id' => $dataRequest->id],
            [
                'data_file_id' => $dataRequest->data_file_id,
                'user_id' => auth()->id(),
                'report_filename' => FileResponse::safeFilename($file->getClientOriginalName(), 'evaluasi-pemanfaatan'),
                'report_path' => $path,
                'report_hash' => $hash,
                'notes' => $request->notes,
                'submitted_at' => now(),
            ]
        );

        $this->audit->log('utilization_evaluation_upload', $evaluation, [
            'data_request_id' => $dataRequest->id,
            'data_file_id' => $dataRequest->data_file_id,
            'report_filename' => $evaluation->report_filename,
        ]);

        return back()->with('success', 'Evaluasi pemanfaatan berhasil diunggah.');
    }

    public function download(DataUtilizationEvaluation $evaluation)
    {
        if ($evaluation->user_id !== auth()->id()) {
            abort(403);
        }

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
