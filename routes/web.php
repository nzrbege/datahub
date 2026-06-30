<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\RegistrationRequestController;
use App\Http\Controllers\DataRequestMonitoringController;
use App\Http\Controllers\Admin\DataRequestController as AdminDataRequestController;
use App\Http\Controllers\Admin\DownloadController;
use App\Http\Controllers\Admin\EvaluationController as AdminEvaluationController;
use App\Http\Controllers\Admin\NdaTemplateController as AdminNdaTemplateController;
use App\Http\Controllers\SuperAdmin\AuditLogController;
use App\Http\Controllers\SuperAdmin\DataFileController;
use App\Http\Controllers\SuperAdmin\DataRequestController as SuperAdminDataRequestController;
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\DownloadPicContactController;
use App\Http\Controllers\SuperAdmin\EvaluationController as SuperAdminEvaluationController;
use App\Http\Controllers\SuperAdmin\NdaTemplateController as SuperAdminNdaTemplateController;
use App\Http\Controllers\SuperAdmin\UserController;
use App\Http\Controllers\SuperAdmin\UserRegistrationRequestController;
use Illuminate\Support\Facades\Route;

// ─── Auth ──────────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::get('/registrasi', [RegistrationRequestController::class, 'create'])->name('register.request');
    Route::post('/registrasi', [RegistrationRequestController::class, 'store'])
        ->middleware('throttle:5,10')
        ->name('register.request.store');
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])
        ->middleware('throttle:5,10')
        ->name('password.email');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/password', [AuthController::class, 'showPasswordForm'])->name('password.edit');
    Route::put('/password', [AuthController::class, 'updatePassword'])->name('password.update');
});

Route::get('/', function () {
    if (auth()->check()) {
        return auth()->user()->isSuperAdmin()
            ? redirect()->route('superadmin.dashboard')
            : redirect()->route('admin.dashboard');
    }
    return redirect()->route('login');
});

Route::middleware('auth')
    ->prefix('monitoring')
    ->name('monitoring.')
    ->group(function () {
        Route::get('/permohonan-data', [DataRequestMonitoringController::class, 'index'])->name('requests.index');
        Route::get('/permohonan-data/{dataRequest}/dokumen/{type}', [DataRequestMonitoringController::class, 'document'])->name('requests.document');
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

        // Template dokumen
        Route::get('/nda-templates', [SuperAdminNdaTemplateController::class, 'index'])->name('nda-templates.index');
        Route::post('/nda-templates', [SuperAdminNdaTemplateController::class, 'store'])->name('nda-templates.store');
        Route::get('/nda-templates/{template}/download', [SuperAdminNdaTemplateController::class, 'download'])->name('nda-templates.download');

        // Kontak PIC password file
        Route::get('/download-pic', [DownloadPicContactController::class, 'edit'])->name('download-pic.edit');
        Route::put('/download-pic', [DownloadPicContactController::class, 'update'])->name('download-pic.update');

        // User management
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::patch('/users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::get('/user-registrations', [UserRegistrationRequestController::class, 'index'])->name('user-registrations.index');
        Route::get('/user-registrations/{registration}/letter', [UserRegistrationRequestController::class, 'letter'])->name('user-registrations.letter');
        Route::post('/user-registrations/{registration}/approve', [UserRegistrationRequestController::class, 'approve'])->name('user-registrations.approve');
        Route::post('/user-registrations/{registration}/reject', [UserRegistrationRequestController::class, 'reject'])->name('user-registrations.reject');

        // Data request review
        Route::get('/requests', [SuperAdminDataRequestController::class, 'index'])->name('requests.index');
        Route::get('/requests/{dataRequest}', [SuperAdminDataRequestController::class, 'show'])->name('requests.show');
        Route::get('/requests/{dataRequest}/nda', [SuperAdminDataRequestController::class, 'nda'])->name('requests.nda');
        Route::get('/requests/{dataRequest}/bast', [SuperAdminDataRequestController::class, 'bast'])->name('requests.bast');
        Route::post('/requests/{dataRequest}/approve', [SuperAdminDataRequestController::class, 'approve'])->name('requests.approve');
        Route::post('/requests/{dataRequest}/return', [SuperAdminDataRequestController::class, 'returnRequest'])->name('requests.return');
        Route::post('/requests/{dataRequest}/reject', [SuperAdminDataRequestController::class, 'reject'])->name('requests.reject');
        Route::post('/requests/{dataRequest}/bast/approve', [SuperAdminDataRequestController::class, 'approveBast'])->name('requests.bast.approve');
        Route::post('/requests/{dataRequest}/bast/return', [SuperAdminDataRequestController::class, 'returnBast'])->name('requests.bast.return');
        Route::post('/requests/{dataRequest}/revoke', [SuperAdminDataRequestController::class, 'revoke'])->name('requests.revoke');
        Route::put('/requests/{dataRequest}/quota', [SuperAdminDataRequestController::class, 'updateQuota'])->name('requests.quota');
        Route::post('/requests/{dataRequest}/quota/reset', [SuperAdminDataRequestController::class, 'resetQuota'])->name('requests.quota.reset');

        // Evaluasi pemanfaatan
        Route::get('/evaluations', [SuperAdminEvaluationController::class, 'index'])->name('evaluations.index');
        Route::get('/evaluations/{evaluation}', [SuperAdminEvaluationController::class, 'show'])->name('evaluations.show');
        Route::get('/evaluations/{evaluation}/download', [SuperAdminEvaluationController::class, 'download'])->name('evaluations.download');

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
        Route::post('/requests/{dataRequest}/bast', [AdminDataRequestController::class, 'uploadBast'])->name('requests.bast.upload');
        Route::get('/templates/{type}/download', [AdminNdaTemplateController::class, 'download'])->name('templates.download');
        Route::get('/nda-template/download', [AdminNdaTemplateController::class, 'download'])->name('nda-template.download');

        // Evaluasi pemanfaatan
        Route::get('/evaluations', [AdminEvaluationController::class, 'index'])->name('evaluations.index');
        Route::post('/evaluations/{dataRequest}', [AdminEvaluationController::class, 'store'])->name('evaluations.store');
        Route::get('/evaluations/{evaluation}/download', [AdminEvaluationController::class, 'download'])->name('evaluations.download');

        // Download dengan captcha
        Route::get('/download/{dataRequest}', [DownloadController::class, 'showDownloadPage'])->name('download.show');
        Route::post('/download/{dataRequest}', [DownloadController::class, 'download'])->name('download.process');
    });

// Captcha refresh route (untuk mews/captcha)
Route::get('/captcha/refresh', function () {
    return response()->json(['captcha' => captcha_img()]);
})->middleware('auth')->name('captcha.refresh');
