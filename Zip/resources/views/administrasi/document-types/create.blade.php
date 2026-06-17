@extends('layouts.app')
@section('title', 'Tambah Jenis Surat')
@section('page-title', 'Tambah Jenis Surat')

@section('content')
<div class="breadcrumb mb-4">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <i class="ph ph-caret-right" style="font-size:12px"></i>
    <a href="{{ route('administrasi.document-types.index') }}">Kelola Jenis Surat</a>
    <i class="ph ph-caret-right" style="font-size:12px"></i>
    <span>Tambah Jenis Surat</span>
</div>

<div class="card max-w-2xl mx-auto">
    <div class="card-title"><i class="ph ph-files"></i> Informasi Jenis Surat</div>
    <form method="POST" action="{{ route('administrasi.document-types.store') }}">
        @csrf
        
        <div class="form-group">
            <label class="form-label" for="unit_id">Unit Terkait <span class="req">*</span></label>
            <select id="unit_id" name="unit_id" class="form-control" required>
                <option value="">— Pilih Unit —</option>
                @foreach($units as $unit)
                    <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                        {{ $unit->name }} ({{ $unit->code }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label class="form-label" for="name">Nama Jenis Surat <span class="req">*</span></label>
            <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" placeholder="Contoh: Surat Keputusan" required>
        </div>

        <div class="form-group">
            <label class="form-label" for="code">Kode Jenis Surat <span class="req">*</span></label>
            <input type="text" id="code" name="code" class="form-control" value="{{ old('code') }}" placeholder="Contoh: SK" required>
            <p class="form-hint text-xs text-gray-500 mt-1">Kode ini akan digunakan pada daftar klasifikasi surat.</p>
        </div>

        <div class="form-group">
            <label class="form-label" for="description">Deskripsi</label>
            <textarea id="description" name="description" class="form-control" rows="3" placeholder="Deskripsi opsional...">{{ old('description') }}</textarea>
        </div>

        <div class="flex gap-3 mt-6">
            <button type="submit" class="btn btn-primary"><i class="ph ph-floppy-disk"></i> Simpan</button>
            <a href="{{ route('administrasi.document-types.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
@endsection
