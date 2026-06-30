<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NdaTemplate;
use App\Services\AuditService;
use App\Support\Security\FileResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NdaTemplateController extends Controller
{
    public function __construct(private AuditService $audit) {}

    public function download(Request $request, ?string $type = null)
    {
        $type = $type ?: $request->query('type', NdaTemplate::TYPE_BAST);
        $allowedTypes = array_keys(NdaTemplate::typeOptions());

        if (!in_array($type, $allowedTypes, true)) {
            abort(404);
        }

        $template = NdaTemplate::active($type);

        if (!$template || !Storage::disk('private')->exists($template->file_path)) {
            return back()->withErrors(['template_dokumen' => "Template {$this->typeLabel($type)} belum tersedia. Hubungi Super Admin."]);
        }

        $this->audit->log(AuditService::ACTION_NDA_TEMPLATE_DOWNLOAD, $template, [
            'template_type' => $template->type_label,
            'template_filename' => $template->original_filename,
        ]);

        return response()->download(
            Storage::disk('private')->path($template->file_path),
            FileResponse::safeFilename($template->original_filename, 'template-dokumen'),
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

    private function typeLabel(string $type): string
    {
        return NdaTemplate::typeOptions()[$type] ?? $type;
    }
}
