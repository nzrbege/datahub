<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\NdaTemplate;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NdaTemplateController extends Controller
{
    public function __construct(private AuditService $audit) {}

    public function index()
    {
        $activeTemplate = NdaTemplate::active();
        $templates = NdaTemplate::with('uploader')
            ->latest()
            ->paginate(10);

        return view('superadmin.nda-templates.index', compact('activeTemplate', 'templates'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'template_file' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ], [
            'template_file.required' => 'File template NDA wajib dipilih.',
            'template_file.mimes' => 'Template NDA harus berformat PDF, DOC, atau DOCX.',
        ]);

        DB::beginTransaction();
        try {
            $file = $request->file('template_file');
            $extension = strtolower($file->getClientOriginalExtension());
            $storedName = Str::random(40) . '.' . $extension;
            $path = $file->storeAs('nda-templates', $storedName, 'private');

            NdaTemplate::where('is_active', true)->update(['is_active' => false]);

            $template = NdaTemplate::create([
                'original_filename' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => $extension,
                'file_size' => $file->getSize(),
                'file_hash' => hash_file('sha256', $file->getRealPath()),
                'is_active' => true,
                'uploaded_by' => auth()->id(),
            ]);

            $this->audit->log(AuditService::ACTION_NDA_TEMPLATE_UPLOAD, $template, [
                'template_filename' => $template->original_filename,
                'file_size' => $template->file_size,
            ]);

            DB::commit();
            return redirect()->route('superadmin.nda-templates.index')
                ->with('success', 'Template NDA berhasil diunggah dan dijadikan template aktif.');
        } catch (\Throwable $e) {
            DB::rollBack();

            if (isset($path) && $path && Storage::disk('private')->exists($path)) {
                Storage::disk('private')->delete($path);
            }

            return back()->withErrors(['template_file' => 'Gagal mengunggah template NDA: ' . $e->getMessage()]);
        }
    }
}
