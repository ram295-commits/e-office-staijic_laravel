@extends('layouts.app')
@section('title', 'Edit Unit')
@section('page-title', 'Edit Unit')

@section('content')
<div class="breadcrumb mb-4">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <i class="ph ph-caret-right" style="font-size:12px"></i>
    <a href="{{ route('administrasi.units.index') }}">Kelola Unit</a>
    <i class="ph ph-caret-right" style="font-size:12px"></i>
    <span>Edit Unit</span>
</div>

<div class="card max-w-2xl mx-auto">
    <div class="card-title"><i class="ph ph-pencil"></i> Edit Informasi Unit</div>
    <form method="POST" action="{{ route('administrasi.units.update', $unit) }}">
        @csrf @method('PUT')
        
        <div class="form-group">
            <label class="form-label" for="name">Nama Unit <span class="req">*</span></label>
            <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $unit->name) }}" required>
        </div>

        <div class="form-group">
            <label class="form-label" for="code">Kode Unit <span class="req">*</span></label>
            <input type="text" id="code" name="code" class="form-control" value="{{ old('code', $unit->code) }}" required>
            <p class="form-hint text-xs text-gray-500 mt-1">Kode ini akan digunakan pada nomor surat.</p>
        </div>

        <div class="form-group">
            <label class="form-label" for="description">Deskripsi</label>
            <textarea id="description" name="description" class="form-control" rows="3">{{ old('description', $unit->description) }}</textarea>
        </div>

        <div class="flex gap-3 mt-6">
            <button type="submit" class="btn btn-primary"><i class="ph ph-floppy-disk"></i> Simpan Perubahan</button>
            <a href="{{ route('administrasi.units.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
@endsection
