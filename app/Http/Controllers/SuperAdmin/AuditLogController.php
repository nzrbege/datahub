<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\DownloadLog;
use App\Models\PersonalDataAudit;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = $this->activityQuery($request)->paginate(50);
        $actionOptions = PersonalDataAudit::actionOptions();

        return view('superadmin.audit.index', compact('logs', 'actionOptions'));
    }

    public function downloadLogs(Request $request)
    {
        $logs = $this->downloadLogQuery()->paginate(50);

        return view('superadmin.audit.downloads', compact('logs'));
    }

    public function export(Request $request)
    {
        $filename = 'log-aktivitas-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($request) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Waktu', 'Nama Pengguna', 'Username', 'Instansi/OPD', 'Aksi', 'Penjelasan Aksi', 'Data Terkait', 'Rincian', 'IP Address', 'Dasar Hukum', 'Tujuan']);

            $this->activityQuery($request)
                ->reorder()
                ->oldest('occurred_at')
                ->chunk(500, function ($logs) use ($handle) {
                    foreach ($logs as $log) {
                        fputcsv($handle, [
                            $log->occurred_at?->format('Y-m-d H:i:s'),
                            $log->user->name ?? 'Sistem',
                            $log->actor_username,
                            $log->actor_institution,
                            $log->action_label,
                            $log->action_description,
                            $log->resource_label,
                            $log->context_summary,
                            $log->ip_address,
                            $log->dasar_hukum,
                            $log->tujuan,
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportDownloads()
    {
        $filename = 'log-download-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Waktu', 'Pengguna', 'Instansi', 'File', 'Nama File Asli', 'IP Address', 'Captcha', 'Status', 'Keterangan']);

            $this->downloadLogQuery()
                ->reorder()
                ->oldest('downloaded_at')
                ->chunk(500, function ($logs) use ($handle) {
                    foreach ($logs as $log) {
                        fputcsv($handle, [
                            $log->downloaded_at?->format('Y-m-d H:i:s'),
                            $log->user->name ?? '',
                            $log->user->instansi ?? '',
                            $log->dataFile->judul ?? '',
                            $log->dataFile->original_filename ?? '',
                            $log->ip_address,
                            $log->captcha_passed ? 'Ya' : 'Tidak',
                            $log->status,
                            $log->keterangan,
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function activityQuery(Request $request)
    {
        $query = PersonalDataAudit::with('user')->latest('occurred_at');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('occurred_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('occurred_at', '<=', $request->date_to);
        }

        return $query;
    }

    private function downloadLogQuery()
    {
        return DownloadLog::with(['user', 'dataFile'])
            ->latest('downloaded_at');
    }
}
