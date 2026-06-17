@extends('layouts.app')
@section('title', 'Tambah ' . ($title ?? 'Surat'))
@section('page-title', 'Tambah ' . ($title ?? 'Surat'))

@section('content')
<div class="breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <i class="ph ph-caret-right" style="font-size:12px"></i>
    @if($type === 'incoming')
        <a href="{{ route('mails.incoming.index') }}">Surat Masuk</a>
    @elseif($type === 'outgoing')
        <a href="{{ route('mails.outgoing.index') }}">Surat Keluar</a>
    @else
        <a href="{{ route('mails.internal.index') }}">Surat Internal</a>
    @endif
    <i class="ph ph-caret-right" style="font-size:12px"></i>
    <span>Tambah</span>
</div>

<form method="POST" action="{{ route('mails.store') }}" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="type" value="{{ $type }}">

    <div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start">
        <!-- Left column -->
        <div>
            <div class="card" style="margin-bottom:16px">
                <div class="card-title"><i class="ph ph-info"></i> Informasi Surat</div>

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
                                    <option value="{{ $typeOption->id }}" {{ old('document_type_id') == $typeOption->id ? 'selected' : '' }}>
                                        {{ $typeOption->code }} - {{ $typeOption->name }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
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

            <div class="card" style="margin-bottom:16px">
                <div class="card-title">
                    <i class="ph ph-user-circle"></i>
                    @if($type === 'incoming') Pengirim @else Penerima @endif
                </div>

                @if($type === 'incoming')
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label" for="sender_name">Nama Pengirim <span class="req">*</span></label>
                        <input type="text" id="sender_name" name="sender_name" class="form-control" value="{{ old('sender_name') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="sender_organization">Instansi Pengirim</label>
                        <input type="text" id="sender_organization" name="sender_organization" class="form-control" value="{{ old('sender_organization') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="sender_email">Email Pengirim</label>
                        <input type="email" id="sender_email" name="sender_email" class="form-control" value="{{ old('sender_email') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="recipient_name">Penerima (Instansi Kami) <span class="req">*</span></label>
                        <input type="text" id="recipient_name" name="recipient_name" class="form-control" value="{{ old('recipient_name', config('app.name')) }}" required>
                    </div>
                </div>
                @else
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label" for="sender_name">Nama Pengirim <span class="req">*</span></label>
                        <input type="text" id="sender_name" name="sender_name" class="form-control" value="{{ old('sender_name', auth()->user()->name) }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="sender_organization">Jabatan / Departemen</label>
                        <input type="text" id="sender_organization" name="sender_organization" class="form-control" value="{{ old('sender_organization', auth()->user()->department) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="recipient_name">Nama Penerima <span class="req">*</span></label>
                        <input type="text" id="recipient_name" name="recipient_name" class="form-control" value="{{ old('recipient_name') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="recipient_department">Departemen Penerima</label>
                        <input type="text" id="recipient_department" name="recipient_department" class="form-control" value="{{ old('recipient_department') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="recipient_email">Email Penerima</label>
                        <input type="email" id="recipient_email" name="recipient_email" class="form-control" value="{{ old('recipient_email') }}">
                    </div>
                </div>
                @endif
            </div>

            <div class="card">
                <div class="card-title"><i class="ph ph-paperclip"></i> Lampiran</div>
                <div class="form-group" style="margin-bottom:0">
                    <input type="file" id="attachment" name="attachment" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                    <p class="form-hint">Maksimal 10MB. Format: PDF, DOC, DOCX, JPG, PNG</p>
                </div>
            </div>
        </div>

        <!-- Right column -->
        <div>
            <div class="card" style="margin-bottom:16px">
                <div class="card-title"><i class="ph ph-calendar-blank"></i> Tanggal</div>

                <div class="form-group">
                    <label class="form-label" for="tanggal_surat">Tanggal Surat <span class="req">*</span></label>
                    <input type="date" id="tanggal_surat" name="tanggal_surat" class="form-control" value="{{ old('tanggal_surat', date('Y-m-d')) }}" required>
                </div>

                @if($type === 'incoming')
                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label" for="received_date">Tanggal Terima</label>
                    <input type="date" id="received_date" name="received_date" class="form-control" value="{{ old('received_date', date('Y-m-d')) }}">
                </div>
                @endif
            </div>

            <div class="card" style="margin-bottom:16px">
                <div class="card-title"><i class="ph ph-tag"></i> Klasifikasi</div>

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

            @if(auth()->user()->isManager())
            <div class="card" style="margin-bottom:16px">
                <div class="card-title"><i class="ph ph-user"></i> Penugasan</div>
                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label" for="assigned_to">Ditugaskan kepada</label>
                    <select id="assigned_to" name="assigned_to" class="form-control">
                        <option value="">— Pilih Petugas —</option>
                        @foreach(\App\Models\User::where('is_active',true)->orderBy('name')->get() as $u)
                            <option value="{{ $u->id }}" {{ old('assigned_to')==$u->id ? 'selected' : '' }}>
                                {{ $u->name }} ({{ $u->role_label }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            @endif

            <div style="display:flex;gap:10px;margin-top:4px">
                <button type="submit" class="btn btn-primary w-full" style="justify-content:center">
                    <i class="ph ph-floppy-disk"></i> Simpan Surat
                </button>
            </div>
            <a href="{{ url()->previous() }}" class="btn btn-secondary w-full" style="justify-content:center;margin-top:8px">
                <i class="ph ph-x"></i> Batal
            </a>
        </div>
    </div>
</form>
@endsection
