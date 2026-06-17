@extends('layouts.app')
@section('title', 'Tambah Pengguna')
@section('page-title', 'Tambah Pengguna')

@section('content')
<div class="breadcrumb">
    <a href="{{ route('administrasi.users.index') }}">Kelola Pengguna</a>
    <i class="ph ph-caret-right" style="font-size:12px"></i>
    <span>Tambah</span>
</div>

<div style="max-width:700px">
    <form method="POST" action="{{ route('administrasi.users.store') }}">
        @csrf
        <div class="card">
            <div class="card-title"><i class="ph ph-user-plus"></i> Informasi Pengguna</div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label" for="name">Nama Lengkap <span class="req">*</span></label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="nip">NIP</label>
                    <input type="text" id="nip" name="nip" class="form-control" value="{{ old('nip') }}" maxlength="30">
                </div>
                <div class="form-group">
                    <label class="form-label" for="email">Email <span class="req">*</span></label>
                    <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="department">Departemen</label>
                    <input type="text" id="department" name="department" class="form-control" value="{{ old('department') }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="position">Jabatan</label>
                    <input type="text" id="position" name="position" class="form-control" value="{{ old('position') }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="role">Peran <span class="req">*</span></label>
                    <select id="role" name="role" class="form-control" required>
                        <option value="staff" {{ old('role','staff')=='staff' ? 'selected':'' }}>Staff</option>
                        <option value="manager" {{ old('role')=='manager' ? 'selected':'' }}>Manager</option>
                        <option value="admin" {{ old('role')=='admin' ? 'selected':'' }}>Administrator</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="password">Password <span class="req">*</span></label>
                    <input type="password" id="password" name="password" class="form-control" required minlength="8">
                    <p class="form-hint">Minimal 8 karakter</p>
                </div>
                <div class="form-group">
                    <label class="form-label" for="password_confirmation">Konfirmasi Password <span class="req">*</span></label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
                </div>
            </div>

            @include('partials.unit-checkboxes', ['units' => $units, 'selectedIds' => collect(old('units', []))])

            <div class="form-group">
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active','1') ? 'checked':'' }} style="width:16px;height:16px;accent-color:var(--accent)">
                    <span class="form-label" style="margin:0">Akun Aktif</span>
                </label>
            </div>

            <div style="display:flex;gap:10px;margin-top:8px">
                <button type="submit" class="btn btn-primary"><i class="ph ph-check"></i> Simpan Pengguna</button>
                <a href="{{ route('administrasi.users.index') }}" class="btn btn-secondary"><i class="ph ph-x"></i> Batal</a>
            </div>
        </div>
    </form>
</div>
@endsection
