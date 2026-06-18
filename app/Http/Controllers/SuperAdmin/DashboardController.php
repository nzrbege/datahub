<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\DataFile;
use App\Models\DataRequest;
use App\Models\DownloadLog;
use App\Models\PersonalDataAudit;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_files'      => DataFile::count(),
            'pending_requests' => DataRequest::where('status', 'pending')->count(),
            'total_admins'     => User::role('admin')->where('is_active', true)->count(),
            'downloads_today'  => DownloadLog::whereDate('downloaded_at', today())->where('status', 'success')->count(),
        ];

        $recentRequests = DataRequest::with(['user', 'dataFile'])
            ->latest()
            ->limit(5)
            ->get();

        $recentActivity = PersonalDataAudit::with('user')
            ->latest('occurred_at')
            ->limit(10)
            ->get();

        return view('superadmin.dashboard', compact('stats', 'recentRequests', 'recentActivity'));
    }
}
