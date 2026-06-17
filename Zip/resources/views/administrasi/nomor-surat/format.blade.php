@extends('layouts.app')
@section('title', 'Pengaturan Format Nomor Surat')
@section('page-title', 'Pengaturan Format Nomor Surat')

@section('content')
<div class="mb-6">
    <a href="{{ route('administrasi.nomor-surat.index') }}" class="text-sm font-semibold text-secondary hover:text-primary transition flex items-center gap-1">
        <i class="ph ph-arrow-left"></i> Kembali ke Manajemen Nomor Surat
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 max-w-4xl mx-auto">
    <div class="mb-6 pb-6 border-b border-gray-100">
        <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2 mb-2">
            <i class="ph ph-faders text-primary"></i> Pengaturan Format Penomoran Institusi
        </h3>
        <p class="text-sm text-gray-500">
            Definisikan struktur penomoran surat. Gunakan *placeholder* berikut yang akan diganti secara otomatis oleh sistem saat surat dibuat atau direvisi:
        </p>
        <div class="mt-4 p-4 bg-gray-50 rounded-lg text-sm font-mono text-gray-700 space-y-2">
            <div><span class="font-bold text-primary">[NO_URUT]</span> : Nomor urut surat (misal: 001)</div>
            <div><span class="font-bold text-primary">[KODE_UNIT]</span> : Kode unit pengirim (misal: Rektorat, Prodi)</div>
            <div><span class="font-bold text-primary">[BULAN_ROMAWI]</span> : Bulan dalam format Romawi (misal: I, II, XII)</div>
            <div><span class="font-bold text-primary">[TAHUN]</span> : Tahun penerbitan (misal: 2026)</div>
        </div>
    </div>

    <form method="POST" action="{{ route('administrasi.nomor-surat.update-format') }}">
        @csrf @method('PUT')
        
        <div class="space-y-5">
            <div class="form-group">
                <label class="form-label">Format Surat Masuk <span class="req">*</span></label>
                <input type="text" name="formats[incoming]" class="form-control font-mono" value="{{ $formats['incoming']->format_string ?? '[NO_URUT]/SM/[KODE_UNIT]/STAI-JIC/[BULAN_ROMAWI]/[TAHUN]' }}" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Format Surat Keluar <span class="req">*</span></label>
                <input type="text" name="formats[outgoing]" class="form-control font-mono" value="{{ $formats['outgoing']->format_string ?? '[NO_URUT]/SK/[KODE_UNIT]/STAI-JIC/[BULAN_ROMAWI]/[TAHUN]' }}" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Format Surat Internal <span class="req">*</span></label>
                <input type="text" name="formats[internal]" class="form-control font-mono" value="{{ $formats['internal']->format_string ?? '[NO_URUT]/SI/[KODE_UNIT]/STAI-JIC/[BULAN_ROMAWI]/[TAHUN]' }}" required>
            </div>

            <div class="pt-4 border-t border-gray-100 flex justify-end">
                <button type="submit" class="btn btn-primary shadow-sm font-bold px-6">
                    <i class="ph ph-floppy-disk"></i> Simpan Format
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
