<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NdaTemplate;
use App\Services\AuditService;
use Illuminate\Support\Facades\Storage;

class NdaTemplateController extends Controller
{
    public function __construct(private AuditService $audit) {}

    public function download()
    {
        $template = NdaTemplate::active();

        if (!$template || !Storage::disk('private')->exists($template->file_path)) {
            return back()->withErrors(['template_nda' => 'Template NDA belum tersedia. Hubungi Super Admin.']);
        }

        $this->audit->log(AuditService::ACTION_NDA_TEMPLATE_DOWNLOAD, $template, [
            'template_filename' => $template->original_filename,
        ]);

        return response()->download(
            Storage::disk('private')->path($template->file_path),
            $template->original_filename,
            ['Content-Type' => $this->mimeType($template->file_type)]
        );
    }

    private function mimeType(string $extension): string
    {
        return match (strtolower($extension)) {
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            default => 'application/octet-stream',
        };
    }
}
