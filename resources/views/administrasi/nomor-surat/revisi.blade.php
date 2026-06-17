@extends('layouts.app')
@section('title', 'Revisi Nomor Surat')
@section('page-title', 'Revisi & Regenerasi Nomor Surat')

@section('content')
<div class="mb-6">
    <a href="{{ route('mails.archive.index') }}" class="text-sm font-semibold text-secondary hover:text-primary transition flex items-center gap-1">
        <i class="ph ph-arrow-left"></i> Kembali ke Arsip
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 max-w-4xl mx-auto">
    <div class="mb-6 pb-6 border-b border-gray-100 flex items-start gap-4">
        <div class="h-12 w-12 rounded-full bg-warning/10 flex items-center justify-center text-warning shrink-0">
            <i class="ph ph-warning-circle text-2xl"></i>
        </div>
        <div>
            <h3 class="text-lg font-bold text-gray-800 mb-1">Revisi Terbatas Nomor Surat</h3>
            <p class="text-sm text-gray-600">
                Anda sedang merevisi surat: <strong>{{ $mail->subject }}</strong>.<br>
                Nomor referensi asli: <span class="font-mono text-primary bg-primary/10 px-1 rounded">{{ $mail->reference_number }}</span>
            </p>
            <p class="text-xs text-red-500 mt-2">
                * Nomor surat baru akan di-generate otomatis oleh sistem berdasarkan formula resmi. Pengubahan manual dilarang.
            </p>
        </div>
    </div>

    <form method="POST" action="{{ route('administrasi.nomor-surat.update-revisi', $mail->id) }}">
        @csrf @method('PATCH')
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
            <div class="form-group">
                <label class="form-label" for="sequence_number">1. Nomor Urut (Sequence) <span class="req">*</span></label>
                <input type="number" min="1" name="sequence_number" id="sequence_number" class="form-control" value="{{ old('sequence_number', $mail->sequence_number) }}" placeholder="Contoh: 12" required>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="sender_unit">2. Kode Unit Pengirim <span class="req">*</span></label>
                <select name="sender_unit" id="sender_unit" class="form-control" required>
                    <option value="">— Pilih Unit —</option>
                    <option value="REKTORAT" {{ old('sender_unit', $mail->sender_unit) == 'REKTORAT' ? 'selected' : '' }}>Rektorat</option>
                    <option value="PRODI" {{ old('sender_unit', $mail->sender_unit) == 'PRODI' ? 'selected' : '' }}>Program Studi</option>
                    <option value="BAAK" {{ old('sender_unit', $mail->sender_unit) == 'BAAK' ? 'selected' : '' }}>BAAK</option>
                    <option value="LEMBAGA" {{ old('sender_unit', $mail->sender_unit) == 'LEMBAGA' ? 'selected' : '' }}>Lembaga Penelitian / Lainnya</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="type">3. Jenis Surat <span class="req">*</span></label>
                <select name="type" id="type" class="form-control" required>
                    <option value="incoming" {{ old('type', $mail->type) == 'incoming' ? 'selected' : '' }}>Surat Masuk</option>
                    <option value="outgoing" {{ old('type', $mail->type) == 'outgoing' ? 'selected' : '' }}>Surat Keluar</option>
                    <option value="internal" {{ old('type', $mail->type) == 'internal' ? 'selected' : '' }}>Surat Internal</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="tanggal_surat">4. Tanggal Surat <span class="req">*</span></label>
                <input type="date" name="tanggal_surat" id="tanggal_surat" class="form-control" value="{{ old('tanggal_surat', $mail->tanggal_surat ? date('Y-m-d', strtotime($mail->tanggal_surat)) : '') }}" required>
            </div>
        </div>

        <div class="form-group mb-6">
            <label class="form-label" for="change_reason">Alasan Revisi <span class="req">*</span></label>
            <input type="text" name="change_reason" id="change_reason" class="form-control" value="{{ old('change_reason') }}" placeholder="Contoh: Kesalahan input jenis surat oleh staff" required>
        </div>

        <div class="pt-4 border-t border-gray-100 flex justify-end">
            <button type="submit" class="btn btn-warning shadow-sm font-bold px-6" onclick="return confirm('Sistem akan men-generate ulang nomor referensi surat berdasarkan parameter ini. Lanjutkan?')">
                <i class="ph ph-arrows-clockwise"></i> Generate & Simpan Revisi
            </button>
        </div>
    </form>
</div>
@endsection
