@extends('layouts.app')
@section('title', 'Isi Slot Nomor Reservasi')
@section('page-title', 'Isi Slot Nomor Reservasi')

@section('content')
<div class="breadcrumb mb-6">
    <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-primary transition-colors">Dashboard</a>
    <i class="ph ph-caret-right text-gray-400" style="font-size:12px; margin: 0 8px;"></i>
    <a href="{{ route('number_reservations.index') }}" class="text-gray-500 hover:text-primary transition-colors">Reservasi Nomor</a>
    <i class="ph ph-caret-right text-gray-400" style="font-size:12px; margin: 0 8px;"></i>
    <span class="text-gray-800 font-medium">Isi Slot</span>
</div>

<form method="POST" action="{{ route('number_reservations.fill_slot', ['reservation' => $reservation->id, 'mail' => $mail->id]) }}">
    @csrf
    @method('PUT')
    
    <!-- Hidden input for immutable date to satisfy validation constraint -->
    <input type="hidden" name="tanggal_surat" value="{{ $mail->tanggal_surat->format('Y-m-d') }}">

    <div style="display:grid; grid-template-columns: 1fr 340px; gap: 24px; align-items: start;">
        <!-- Left Column -->
        <div class="space-y-6">
            <div class="card">
                <div class="card-title">
                    <i class="ph ph-info text-primary text-xl"></i> 
                    Isi Informasi Surat
                </div>

                <div class="form-group">
                    <label class="form-label">Nomor Surat (Reference Number)</label>
                    <input type="text" class="form-control bg-gray-100 border-gray-300 text-gray-500 font-mono cursor-not-allowed select-all" value="{{ $mail->reference_number }}" disabled>
                    <span class="text-xs text-gray-400 mt-1 block">Nomor surat telah digenerasikan secara otomatis berdasarkan urutan sequence.</span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="subject">Perihal <span class="req">*</span></label>
                    <input type="text" id="subject" name="subject" class="form-control" value="{{ old('subject') }}" placeholder="Perihal surat..." required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="body">Isi / Ringkasan Surat <span class="req">*</span></label>
                    <textarea id="body" name="body" class="form-control" rows="6" placeholder="Isi atau ringkasan surat..." required>{{ old('body') }}</textarea>
                </div>

                <div class="form-group">
                    <label class="form-label" for="notes">Catatan Tambahan</label>
                    <textarea id="notes" name="notes" class="form-control" rows="3" placeholder="Catatan internal...">{{ old('notes') }}</textarea>
                </div>
            </div>

            <!-- Identity block: Sender & Recipient details -->
            <div class="card">
                <div class="card-title">
                    <i class="ph ph-user-circle text-primary text-xl"></i> 
                    Detail Pengirim & Penerima
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label" for="sender_name">Nama Pengirim <span class="req">*</span></label>
                        <input type="text" id="sender_name" name="sender_name" class="form-control" value="{{ old('sender_name') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="sender_organization">Instansi / Organisasi Pengirim</label>
                        <input type="text" id="sender_organization" name="sender_organization" class="form-control" value="{{ old('sender_organization') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="sender_email">Email Pengirim</label>
                        <input type="email" id="sender_email" name="sender_email" class="form-control" value="{{ old('sender_email') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="recipient_name">Nama Penerima <span class="req">*</span></label>
                        <input type="text" id="recipient_name" name="recipient_name" class="form-control" value="{{ old('recipient_name') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="recipient_department">Departemen / Unit Penerima</label>
                        <input type="text" id="recipient_department" name="recipient_department" class="form-control" value="{{ old('recipient_department') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="recipient_email">Email Penerima</label>
                        <input type="email" id="recipient_email" name="recipient_email" class="form-control" value="{{ old('recipient_email') }}">
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="space-y-6">
            <!-- Locked Date Card -->
            <div class="card">
                <div class="card-title">
                    <i class="ph ph-calendar-blank text-primary text-xl"></i> 
                    Tanggal Surat
                </div>

                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label">Tanggal Surat (Locked)</label>
                    <input type="date" class="form-control bg-gray-150 border-gray-300 text-gray-500 font-medium cursor-not-allowed select-none opacity-60" value="{{ $mail->tanggal_surat->format('Y-m-d') }}" disabled>
                    <span class="text-xs text-red-600 font-semibold mt-2 flex items-center gap-1 bg-red-50 border border-red-100 rounded px-2 py-1.5">
                        <i class="ph ph-lock-key text-base"></i> Tanggal terkunci oleh sistem.
                    </span>
                </div>
            </div>

            <!-- Classification Card -->
            <div class="card">
                <div class="card-title">
                    <i class="ph ph-tag text-primary text-xl"></i> 
                    Klasifikasi
                </div>

                <div class="form-group">
                    <label class="form-label" for="priority">Prioritas <span class="req">*</span></label>
                    <select id="priority" name="priority" class="form-control" required>
                        <option value="normal" {{ old('priority','normal')=='normal' ? 'selected' : '' }}>Biasa</option>
                        <option value="urgent" {{ old('priority')=='urgent' ? 'selected' : '' }}>Mendesak</option>
                        <option value="very_urgent" {{ old('priority')=='very_urgent' ? 'selected' : '' }}>Sangat Mendesak</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label" for="classification">Sifat Surat <span class="req">*</span></label>
                    <select id="classification" name="classification" class="form-control" required>
                        <option value="open" {{ old('classification','open')=='open' ? 'selected' : '' }}>Terbuka</option>
                        <option value="confidential" {{ old('classification')=='confidential' ? 'selected' : '' }}>Rahasia</option>
                        <option value="secret" {{ old('classification')=='secret' ? 'selected' : '' }}>Sangat Rahasia</option>
                    </select>
                </div>
            </div>

            <!-- Submit Options -->
            <div class="space-y-2">
                <button type="submit" class="btn btn-primary w-full justify-center">
                    <i class="ph ph-floppy-disk"></i> Simpan & Kirim Slot
                </button>
                <a href="{{ route('number_reservations.index') }}" class="btn btn-secondary w-full justify-center">
                    <i class="ph ph-arrow-left"></i> Kembali ke Daftar
                </a>
            </div>
        </div>
    </div>
</form>
@endsection
