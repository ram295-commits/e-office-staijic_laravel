@extends('layouts.app')
@section('title', 'Edit Surat — ' . $mail->reference_number)
@section('page-title', 'Edit Surat')

@section('content')
<div class="breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <i class="ph ph-caret-right" style="font-size:12px"></i>
    <a href="{{ route('mails.show', $mail) }}">{{ $mail->reference_number }}</a>
    <i class="ph ph-caret-right" style="font-size:12px"></i>
    <span>Edit</span>
</div>

<form method="POST" action="{{ route('mails.update', $mail) }}" enctype="multipart/form-data">
    @csrf @method('PUT')

    <div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start">
        <div>
            <div class="card" style="margin-bottom:16px">
                <div class="card-title"><i class="ph ph-info"></i> Informasi Surat</div>

                <div class="form-group">
                    <label class="form-label">Nomor Referensi</label>
                    <input type="text" class="form-control" value="{{ $mail->reference_number }}" disabled style="opacity:0.6">
                </div>

                <div class="form-group">
                    <label class="form-label" for="document_type_id">Jenis Dokumen <span class="req">*</span></label>
                    <select id="document_type_id" name="document_type_id" class="form-control" required>
                        <option value="">— Pilih Jenis Dokumen —</option>
                        @php
                            $userUnits = auth()->user()->isAdmin() ? \App\Models\Unit::all() : auth()->user()->units;
                            $allowedUnitIds = $userUnits->pluck('id');
                            $documentTypes = \App\Models\DocumentType::whereIn('unit_id', $allowedUnitIds)->with('unit')->get()->groupBy('unit.name');
                        @endphp
                        @foreach($documentTypes as $unitName => $types)
                            <optgroup label="{{ $unitName }}">
                                @foreach($types as $typeOption)
                                    <option value="{{ $typeOption->id }}" {{ old('document_type_id', $mail->document_type_id) == $typeOption->id ? 'selected' : '' }}>
                                        {{ $typeOption->code }} - {{ $typeOption->name }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="subject">Perihal <span class="req">*</span></label>
                    <input type="text" id="subject" name="subject" class="form-control" value="{{ old('subject', $mail->subject) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="body">Isi / Ringkasan <span class="req">*</span></label>
                    <textarea id="body" name="body" class="form-control" rows="6" required>{{ old('body', $mail->body) }}</textarea>
                </div>

                <div class="form-group">
                    <label class="form-label" for="notes">Catatan</label>
                    <textarea id="notes" name="notes" class="form-control" rows="3">{{ old('notes', $mail->notes) }}</textarea>
                </div>
            </div>

            <div class="card" style="margin-bottom:16px">
                <div class="card-title"><i class="ph ph-user-circle"></i> Pengirim & Penerima</div>
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label" for="sender_name">Pengirim <span class="req">*</span></label>
                        <input type="text" id="sender_name" name="sender_name" class="form-control" value="{{ old('sender_name', $mail->sender_name) }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="sender_organization">Instansi Pengirim</label>
                        <input type="text" id="sender_organization" name="sender_organization" class="form-control" value="{{ old('sender_organization', $mail->sender_organization) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="recipient_name">Penerima <span class="req">*</span></label>
                        <input type="text" id="recipient_name" name="recipient_name" class="form-control" value="{{ old('recipient_name', $mail->recipient_name) }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="recipient_department">Departemen Penerima</label>
                        <input type="text" id="recipient_department" name="recipient_department" class="form-control" value="{{ old('recipient_department', $mail->recipient_department) }}">
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-title"><i class="ph ph-paperclip"></i> Lampiran</div>
                @if($mail->attachment_path)
                    <div style="margin-bottom:12px;display:flex;align-items:center;gap:10px">
                        <a href="{{ asset('storage/'.$mail->attachment_path) }}" target="_blank" class="btn btn-secondary btn-sm">
                            <i class="ph ph-paperclip"></i> {{ $mail->attachment_name }}
                        </a>
                        <span class="text-xs text-muted">Unggah file baru untuk mengganti</span>
                    </div>
                @endif
                <input type="file" name="attachment" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
            </div>
        </div>

        <div>
            <div class="card" style="margin-bottom:16px">
                <div class="card-title"><i class="ph ph-calendar-blank"></i> Tanggal</div>
                <div class="form-group">
                    <label class="form-label" for="tanggal_surat">Tanggal Surat <span class="req">*</span></label>
                    <input type="date" id="tanggal_surat" name="tanggal_surat" class="form-control" value="{{ old('tanggal_surat', $mail->tanggal_surat->format('Y-m-d')) }}" required>
                </div>
                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label" for="received_date">Tanggal Terima</label>
                    <input type="date" id="received_date" name="received_date" class="form-control" value="{{ old('received_date', $mail->received_date?->format('Y-m-d')) }}">
                </div>
            </div>

            <div class="card" style="margin-bottom:16px">
                <div class="card-title"><i class="ph ph-tag"></i> Klasifikasi & Status</div>
                <div class="form-group">
                    <label class="form-label" for="priority">Prioritas</label>
                    <select id="priority" name="priority" class="form-control">
                        <option value="normal" {{ old('priority',$mail->priority)=='normal' ? 'selected':'' }}>Biasa</option>
                        <option value="urgent" {{ old('priority',$mail->priority)=='urgent' ? 'selected':'' }}>Mendesak</option>
                        <option value="very_urgent" {{ old('priority',$mail->priority)=='very_urgent' ? 'selected':'' }}>Sangat Mendesak</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="classification">Sifat Surat</label>
                    <select id="classification" name="classification" class="form-control">
                        <option value="open" {{ old('classification',$mail->classification)=='open' ? 'selected':'' }}>Terbuka</option>
                        <option value="confidential" {{ old('classification',$mail->classification)=='confidential' ? 'selected':'' }}>Rahasia</option>
                        <option value="secret" {{ old('classification',$mail->classification)=='secret' ? 'selected':'' }}>Sangat Rahasia</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label" for="status">Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="draft" {{ old('status',$mail->status)=='draft' ? 'selected':'' }}>Draft</option>
                        <option value="pending" {{ old('status',$mail->status)=='pending' ? 'selected':'' }}>Pending</option>
                        <option value="in_progress" {{ old('status',$mail->status)=='in_progress' ? 'selected':'' }}>Diproses</option>
                        <option value="completed" {{ old('status',$mail->status)=='completed' ? 'selected':'' }}>Selesai</option>
                        <option value="archived" {{ old('status',$mail->status)=='archived' ? 'selected':'' }}>Diarsipkan</option>
                    </select>
                </div>
            </div>

            @if(auth()->user()->isManager())
            <div class="card" style="margin-bottom:16px">
                <div class="card-title"><i class="ph ph-user"></i> Penugasan</div>
                <div class="form-group" style="margin-bottom:0">
                    <select name="assigned_to" class="form-control">
                        <option value="">— Tidak Ditugaskan —</option>
                        @foreach(\App\Models\User::where('is_active',true)->orderBy('name')->get() as $u)
                            <option value="{{ $u->id }}" {{ old('assigned_to',$mail->assigned_to)==$u->id ? 'selected':'' }}>
                                {{ $u->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            @endif

            <button type="submit" class="btn btn-primary w-full" style="justify-content:center">
                <i class="ph ph-floppy-disk"></i> Simpan Perubahan
            </button>
            <a href="{{ route('mails.show', $mail) }}" class="btn btn-secondary w-full" style="justify-content:center;margin-top:8px">
                <i class="ph ph-x"></i> Batal
            </a>
        </div>
    </div>
</form>
@endsection
