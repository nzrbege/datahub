<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\DataRequestController as AdminDataRequestController;
use App\Http\Controllers\Admin\DownloadController;
use App\Http\Controllers\SuperAdmin\AuditLogController;
use App\Http\Controllers\SuperAdmin\DataFileController;
use App\Http\Controllers\SuperAdmin\DataRequestController as SuperAdminDataRequestController;
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\UserController;
use Illuminate\Support\Facades\Route;

// ─── Auth ──────────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::get('/', function () {
    if (auth()->check()) {
        return auth()->user()->isSuperAdmin()
            ? redirect()->route('superadmin.dashboard')
            : redirect()->route('admin.dashboard');
    }
    return redirect()->route('login');
});

// ─── Super Admin Routes ────────────────────────────────────────────────────────
Route::prefix('superadmin')
    ->name('superadmin.')
    ->middleware(['auth', 'role:super_admin'])
    ->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // File management
        Route::get('/files', [DataFileController::class, 'index'])->name('files.index');
        Route::get('/files/create', [DataFileController::class, 'create'])->name('files.create');
        Route::post('/files', [DataFileController::class, 'store'])->name('files.store');
        Route::get('/files/{dataFile}', [DataFileController::class, 'show'])->name('files.show');
        Route::get('/files/{dataFile}/download', [DataFileController::class, 'download'])->name('files.download');
        Route::put('/files/{dataFile}/permissions', [DataFileController::class, 'updatePermissions'])->name('files.permissions');
        Route::delete('/files/{dataFile}', [DataFileController::class, 'destroy'])->name('files.destroy');

        // User management
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::patch('/users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');

        // Data request review
        Route::get('/requests', [SuperAdminDataRequestController::class, 'index'])->name('requests.index');
        Route::get('/requests/{dataRequest}', [SuperAdminDataRequestController::class, 'show'])->name('requests.show');
        Route::get('/requests/{dataRequest}/nda', [SuperAdminDataRequestController::class, 'nda'])->name('requests.nda');
        Route::post('/requests/{dataRequest}/approve', [SuperAdminDataRequestController::class, 'approve'])->name('requests.approve');
        Route::post('/requests/{dataRequest}/reject', [SuperAdminDataRequestController::class, 'reject'])->name('requests.reject');
        Route::post('/requests/{dataRequest}/revoke', [SuperAdminDataRequestController::class, 'revoke'])->name('requests.revoke');
        Route::put('/requests/{dataRequest}/quota', [SuperAdminDataRequestController::class, 'updateQuota'])->name('requests.quota');
        Route::post('/requests/{dataRequest}/quota/reset', [SuperAdminDataRequestController::class, 'resetQuota'])->name('requests.quota.reset');

        // Log aktivitas
        Route::get('/audit', [AuditLogController::class, 'index'])->name('audit.index');
        Route::get('/audit/export', [AuditLogController::class, 'export'])->name('audit.export');
        Route::get('/audit/downloads', [AuditLogController::class, 'downloadLogs'])->name('audit.downloads');
        Route::get('/audit/downloads/export', [AuditLogController::class, 'exportDownloads'])->name('audit.downloads.export');
    });

// ─── Admin Routes ──────────────────────────────────────────────────────────────
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:admin'])
    ->group(function () {

        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');

        // Permintaan data
        Route::get('/requests', [AdminDataRequestController::class, 'index'])->name('requests.index');
        Route::get('/requests/create', [AdminDataRequestController::class, 'create'])->name('requests.create');
        Route::post('/requests', [AdminDataRequestController::class, 'store'])->name('requests.store');
        Route::get('/requests/{dataRequest}', [AdminDataRequestController::class, 'show'])->name('requests.show');
        Route::get('/requests/{dataRequest}/edit', [AdminDataRequestController::class, 'edit'])->name('requests.edit');
        Route::put('/requests/{dataRequest}', [AdminDataRequestController::class, 'update'])->name('requests.update');

        // Download dengan captcha
        Route::get('/download/{dataRequest}', [DownloadController::class, 'showDownloadPage'])->name('download.show');
        Route::post('/download/{dataRequest}', [DownloadController::class, 'download'])->name('download.process');
    });

// Captcha refresh route (untuk mews/captcha)
Route::get('/captcha/refresh', function () {
    return response()->json(['captcha' => captcha_img()]);
})->middleware('auth')->name('captcha.refresh');
