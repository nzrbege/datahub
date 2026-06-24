<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\DownloadPicContact;
use App\Services\AuditService;
use Illuminate\Http\Request;

class DownloadPicContactController extends Controller
{
    public function __construct(private AuditService $audit) {}

    public function edit()
    {
        $contact = DownloadPicContact::current();

        return view('superadmin.download-pic.edit', compact('contact'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'nama_pic' => ['required', 'string', 'max:255'],
            'nomor_hp' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s()]+$/'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ], [
            'nomor_hp.regex' => 'Nomor HP hanya boleh berisi angka, spasi, tanda +, tanda -, dan kurung.',
        ]);

        $contact = DownloadPicContact::current();

        if ($contact) {
            $contact->update($data + ['updated_by' => auth()->id()]);
        } else {
            $contact = DownloadPicContact::create($data + ['updated_by' => auth()->id()]);
        }

        $this->audit->log(AuditService::ACTION_DOWNLOAD_PIC_UPDATE, $contact, [
            'nama_pic' => $contact->nama_pic,
            'nomor_hp' => $contact->nomor_hp,
        ]);

        return redirect()->route('superadmin.download-pic.edit')
            ->with('success', 'Kontak PIC password file berhasil diperbarui.');
    }
}
