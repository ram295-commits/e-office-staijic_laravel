@extends('layouts.app')
@section('title', 'Edit Pengguna — ' . $user->name)
@section('page-title', 'Edit Pengguna')

@section('content')
<div class="breadcrumb">
    <a href="{{ route('administrasi.users.index') }}">Kelola Pengguna</a>
    <i class="ph ph-caret-right" style="font-size:12px"></i>
    <a href="{{ route('administrasi.users.show', $user) }}">{{ $user->name }}</a>
    <i class="ph ph-caret-right" style="font-size:12px"></i>
    <span>Edit</span>
</div>

<div style="max-width:700px">
    <form method="POST" action="{{ route('administrasi.users.update', $user) }}">
        @csrf @method('PUT')
        <div class="card">
            <div class="card-title"><i class="ph ph-pencil"></i> Edit Data Pengguna</div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label" for="name">Nama Lengkap <span class="req">*</span></label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="nip">NIP</label>
                    <input type="text" id="nip" name="nip" class="form-control" value="{{ old('nip', $user->nip) }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="email">Email <span class="req">*</span></label>
                    <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="department">Departemen</label>
                    <input type="text" id="department" name="department" class="form-control" value="{{ old('department', $user->department) }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="position">Jabatan</label>
                    <input type="text" id="position" name="position" class="form-control" value="{{ old('position', $user->position) }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="role">Peran <span class="req">*</span></label>
                    <select id="role" name="role" class="form-control" required>
                        <option value="staff" {{ old('role',$user->role)=='staff' ? 'selected':'' }}>Staff</option>
                        <option value="manager" {{ old('role',$user->role)=='manager' ? 'selected':'' }}>Manager</option>
                        <option value="admin" {{ old('role',$user->role)=='admin' ? 'selected':'' }}>Administrator</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="password">Password Baru</label>
                    <input type="password" id="password" name="password" class="form-control" minlength="8">
                    <p class="form-hint">Kosongkan jika tidak ingin mengubah password</p>
                </div>
                <div class="form-group">
                    <label class="form-label" for="password_confirmation">Konfirmasi Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control">
                </div>
            </div>

            @include('partials.unit-checkboxes', ['units' => $units, 'selectedIds' => collect(old('units', $user->units->pluck('id')->toArray()))])

            <div class="form-group">
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked':'' }} style="width:16px;height:16px;accent-color:var(--accent)">
                    <span class="form-label" style="margin:0">Akun Aktif</span>
                </label>
            </div>

            <div style="display:flex;gap:10px;margin-top:8px">
                <button type="submit" class="btn btn-primary"><i class="ph ph-floppy-disk"></i> Simpan Perubahan</button>
                <a href="{{ route('administrasi.users.show', $user) }}" class="btn btn-secondary"><i class="ph ph-x"></i> Batal</a>
            </div>
        </div>
    </form>
</div>
@endsection
