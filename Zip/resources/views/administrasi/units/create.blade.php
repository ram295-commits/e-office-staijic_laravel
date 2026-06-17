@extends('layouts.app')
@section('title', 'Tambah Unit')
@section('page-title', 'Tambah Unit')

@section('content')
<div class="breadcrumb mb-4">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <i class="ph ph-caret-right" style="font-size:12px"></i>
    <a href="{{ route('administrasi.units.index') }}">Kelola Unit</a>
    <i class="ph ph-caret-right" style="font-size:12px"></i>
    <span>Tambah Unit</span>
</div>

<div class="card max-w-2xl mx-auto">
    <div class="card-title"><i class="ph ph-buildings"></i> Informasi Unit</div>
    <form method="POST" action="{{ route('administrasi.units.store') }}">
        @csrf
        
        <div class="form-group">
            <label class="form-label" for="name">Nama Unit <span class="req">*</span></label>
            <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" placeholder="Contoh: Bidang 1 Akademik" required>
        </div>

        <div class="form-group">
            <label class="form-label" for="code">Kode Unit <span class="req">*</span></label>
            <input type="text" id="code" name="code" class="form-control" value="{{ old('code') }}" placeholder="Contoh: AKD" required>
            <p class="form-hint text-xs text-gray-500 mt-1">Kode ini akan digunakan pada nomor surat (contoh: .../AKD/...). Pastikan unik.</p>
        </div>

        <div class="form-group">
            <label class="form-label" for="description">Deskripsi</label>
            <textarea id="description" name="description" class="form-control" rows="3" placeholder="Deskripsi opsional...">{{ old('description') }}</textarea>
        </div>

        <div class="flex gap-3 mt-6">
            <button type="submit" class="btn btn-primary"><i class="ph ph-floppy-disk"></i> Simpan</button>
            <a href="{{ route('administrasi.units.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
@endsection
