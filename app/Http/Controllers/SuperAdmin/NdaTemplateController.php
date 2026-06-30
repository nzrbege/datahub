<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\NdaTemplate;
use App\Services\AuditService;
use App\Support\Security\FileResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NdaTemplateController extends Controller
{
    public function __construct(private AuditService $audit) {}

    public function index()
    {
        $activeTemplates = [
            NdaTemplate::TYPE_REQUEST_LETTER => NdaTemplate::active(NdaTemplate::TYPE_REQUEST_LETTER),
            NdaTemplate::TYPE_BAST => NdaTemplate::active(NdaTemplate::TYPE_BAST),
        ];
        $typeOptions = NdaTemplate::typeOptions();
        $templates = NdaTemplate::with('uploader')
            ->latest()
            ->paginate(10);

        return view('superadmin.nda-templates.index', compact('activeTemplates', 'typeOptions', 'templates'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'template_type' => ['required', 'in:' . implode(',', array_keys(NdaTemplate::typeOptions()))],
            'template_file' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ], [
            'template_type.required' => 'Jenis template wajib dipilih.',
            'template_type.in' => 'Jenis template tidak valid.',
            'template_file.required' => 'File template wajib dipilih.',
            'template_file.mimes' => 'Template harus berformat PDF, DOC, atau DOCX.',
        ]);

        DB::beginTransaction();
        try {
            $templateType = $request->template_type;
            $file = $request->file('template_file');
            $extension = strtolower($file->getClientOriginalExtension());
            $storedName = Str::random(40) . '.' . $extension;
            $path = $file->storeAs('document-templates', $storedName, 'private');

            NdaTemplate::where('template_type', $templateType)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            $template = NdaTemplate::create([
                'template_type' => $templateType,
                'original_filename' => FileResponse::safeFilename($file->getClientOriginalName(), 'template-dokumen'),
                'file_path' => $path,
                'file_type' => $extension,
                'file_size' => $file->getSize(),
                'file_hash' => hash_file('sha256', $file->getRealPath()),
                'is_active' => true,
                'uploaded_by' => auth()->id(),
            ]);

            $this->audit->log(AuditService::ACTION_NDA_TEMPLATE_UPLOAD, $template, [
                'template_type' => $template->type_label,
                'template_filename' => $template->original_filename,
                'file_size' => $template->file_size,
            ]);

            DB::commit();
            return redirect()->route('superadmin.nda-templates.index')
                ->with('success', "Template {$template->type_label} berhasil diunggah dan dijadikan template aktif.");
        } catch (\Throwable $e) {
            DB::rollBack();

            if (isset($path) && $path && Storage::disk('private')->exists($path)) {
                Storage::disk('private')->delete($path);
            }

            return back()->withErrors(['template_file' => 'Gagal mengunggah template: ' . $e->getMessage()]);
        }
    }

    public function download(NdaTemplate $template)
    {
        if (!Storage::disk('private')->exists($template->file_path)) {
            return back()->withErrors(['template_file' => 'File template tidak ditemukan di storage.']);
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
}
