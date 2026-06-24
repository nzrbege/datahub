@extends('layouts.app')
@section('page-title', 'Kontak PIC Password File')

@section('content')
<div class="pdp-notice">
    <i class="fas fa-user-lock"></i>
    <div><strong>Kontak PIC Unduhan:</strong> Admin OPD akan melihat kontak ini di halaman konfirmasi unduhan untuk menanyakan password pembuka file dataset.</div>
</div>

<div style="max-width:680px;">
    <div class="card">
        <div class="card-header">
            <span class="card-title"><i class="fas fa-address-card"></i> Atur Kontak PIC</span>
        </div>
        <div class="card-body">
            @if($contact)
                <div style="background:var(--info-bg); border:1px solid var(--info-border); border-radius:var(--radius-sm); padding:13px 15px; margin-bottom:18px;">
                    <div style="font-size:12px; font-weight:700; color:#1e3a8a; margin-bottom:4px;">Kontak aktif saat ini</div>
                    <div style="font-size:13px; color:var(--text); font-weight:700;">{{ $contact->nama_pic }}</div>
                    <div style="font-size:12px; color:var(--text-muted); margin-top:2px;">{{ $contact->nomor_hp }}</div>
                    <div style="font-size:11px; color:var(--text-ghost); margin-top:6px;">
                        Terakhir diperbarui {{ $contact->updated_at->format('d/m/Y H:i') }} oleh {{ $contact->updater->name ?? '-' }}
                    </div>
                </div>
            @endif

            <form action="{{ route('superadmin.download-pic.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label class="form-label">Nama PIC <span class="required">*</span></label>
                    <input type="text" name="nama_pic" class="form-control" value="{{ old('nama_pic', $contact->nama_pic ?? '') }}" required placeholder="mis: Bapak/Ibu PIC Data">
                    @error('nama_pic')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Nomor HP / WhatsApp <span class="required">*</span></label>
                    <input type="text" name="nomor_hp" class="form-control" value="{{ old('nomor_hp', $contact->nomor_hp ?? '') }}" required placeholder="mis: 0812-3456-7890">
                    <div class="form-text">Nomor ini akan ditampilkan kepada Admin OPD sebelum mengunduh file.</div>
                    @error('nomor_hp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Catatan Tambahan</label>
                    <textarea name="keterangan" class="form-control" rows="3" placeholder="mis: Hubungi pada jam kerja.">{{ old('keterangan', $contact->keterangan ?? '') }}</textarea>
                    @error('keterangan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-floppy-disk"></i> Simpan Kontak PIC
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
