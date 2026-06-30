<?php

namespace App\Services;

use App\Models\PersonalDataAudit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * AuditService - Pencatatan aktivitas sesuai UU PDP No.27/2022
 *
 * Pasal 47: Pengendali data wajib membuat catatan aktivitas pemrosesan data pribadi.
 * Pasal 35: Langkah-langkah keamanan yang tepat termasuk pencatatan akses.
 * Pasal 53: Pelanggaran wajib dilaporkan dalam 14 hari.
 */
class AuditService
{
    const ACTION_LOGIN          = 'login';
    const ACTION_LOGOUT         = 'logout';
    const ACTION_LOGIN_FAILED   = 'login_failed';
    const ACTION_FILE_UPLOAD    = 'file_upload';
    const ACTION_FILE_DELETE    = 'file_delete';
    const ACTION_FILE_VIEW      = 'file_view';
    const ACTION_FILE_DOWNLOAD  = 'file_download';
    const ACTION_REQUEST_CREATE = 'request_create';
    const ACTION_REQUEST_REVISE = 'request_revise';
    const ACTION_REQUEST_APPROVE= 'request_approve';
    const ACTION_REQUEST_REJECT = 'request_reject';
    const ACTION_REQUEST_REVOKE = 'request_revoke';
    const ACTION_NDA_VIEW       = 'nda_view';
    const ACTION_NDA_DOWNLOAD   = 'nda_download';
    const ACTION_NDA_TEMPLATE_UPLOAD   = 'nda_template_upload';
    const ACTION_NDA_TEMPLATE_DOWNLOAD = 'nda_template_download';
    const ACTION_DOWNLOAD_PIC_UPDATE   = 'download_pic_update';
    const ACTION_QUOTA_UPDATE   = 'quota_update';
    const ACTION_QUOTA_RESET    = 'quota_reset';
    const ACTION_PERMISSION_GRANT  = 'permission_grant';
    const ACTION_PERMISSION_REVOKE = 'permission_revoke';
    const ACTION_USER_CREATE    = 'user_create';
    const ACTION_USER_UPDATE    = 'user_update';
    const ACTION_USER_DEACTIVATE= 'user_deactivate';

    public function log(
        string  $action,
        ?Model  $resource = null,
        array   $context = [],
        ?string $dasarHukum = null,
        ?string $tujuan = null
    ): void {
        try {
            $userId = auth()->id();

            if (!$userId) {
                Log::channel('audit')->info("PDP_AUDIT: {$action}", [
                    'user_id'  => null,
                    'resource' => $resource ? class_basename($resource) . '#' . $resource->getKey() : null,
                    'ip'       => request()->ip(),
                    'context'  => array_merge($context, [
                        'url'    => request()->fullUrl(),
                        'method' => request()->method(),
                    ]),
                ]);

                return;
            }

            $data = [
                'user_id'       => $userId,
                'action'        => $action,
                'resource_type' => $resource ? class_basename($resource) : null,
                'resource_id'   => $resource?->getKey(),
                'context'       => array_merge($context, [
                    'url'    => request()->fullUrl(),
                    'method' => request()->method(),
                ]),
                'ip_address'    => request()->ip(),
                'user_agent'    => request()->userAgent() ?? '',
                'dasar_hukum'   => $dasarHukum,
                'tujuan'        => $tujuan,
                'occurred_at'   => now(),
            ];

            PersonalDataAudit::create($data);

            // Juga log ke Laravel log untuk backup audit trail
            Log::channel('audit')->info("PDP_AUDIT: {$action}", [
                'user_id'  => $userId,
                'resource' => $resource ? class_basename($resource) . '#' . $resource->getKey() : null,
                'ip'       => request()->ip(),
            ]);
        } catch (\Exception $e) {
            // Jangan biarkan kegagalan audit menghentikan operasi,
            // tapi log ke sistem log utama
            Log::critical('AUDIT_FAIL: ' . $e->getMessage(), ['action' => $action]);
        }
    }

    public function logDownload(
        int    $userId,
        int    $fileId,
        ?int   $requestId,
        bool   $captchaPassed,
        string $status,
        string $keterangan = ''
    ): void {
        \App\Models\DownloadLog::create([
            'user_id'        => $userId,
            'data_file_id'   => $fileId,
            'data_request_id'=> $requestId,
            'ip_address'     => request()->ip(),
            'user_agent'     => request()->userAgent() ?? '',
            'captcha_passed' => $captchaPassed,
            'status'         => $status,
            'keterangan'     => $keterangan,
            'downloaded_at'  => now(),
        ]);
    }
}
