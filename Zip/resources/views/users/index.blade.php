@extends('layouts.app')
@section('title', 'Kelola Pengguna')
@section('page-title', 'Kelola Pengguna')

@section('header-actions')
    <a href="{{ route('administrasi.users.create') }}" class="btn btn-primary"><i class="ph ph-user-plus"></i> Tambah Pengguna</a>
@endsection

@section('content')
<div class="card">
    <form method="GET" id="filterForm">
        <div class="filter-bar">
            <div class="search-wrap">
                <i class="ph ph-magnifying-glass"></i>
                <input type="text" name="search" class="form-control" placeholder="Cari nama, email, NIP..." value="{{ request('search') }}">
            </div>
            <select name="role" class="form-control" onchange="document.getElementById('filterForm').submit()">
                <option value="">Semua Peran</option>
                <option value="admin" {{ request('role')=='admin' ? 'selected':'' }}>Administrator</option>
                <option value="manager" {{ request('role')=='manager' ? 'selected':'' }}>Manager</option>
                <option value="staff" {{ request('role')=='staff' ? 'selected':'' }}>Staff</option>
            </select>
            <select name="status" class="form-control" onchange="document.getElementById('filterForm').submit()">
                <option value="">Semua Status</option>
                <option value="active" {{ request('status')=='active' ? 'selected':'' }}>Aktif</option>
                <option value="inactive" {{ request('status')=='inactive' ? 'selected':'' }}>Nonaktif</option>
            </select>
            <button type="submit" class="btn btn-secondary"><i class="ph ph-funnel"></i> Filter</button>
            @if(request()->hasAny(['search','role','status']))
                <a href="{{ route('administrasi.users.index') }}" class="btn btn-secondary"><i class="ph ph-x"></i> Reset</a>
            @endif
        </div>
    </form>

    @if($users->isEmpty())
        <div style="text-align:center;padding:50px 0;color:var(--text-muted)">
            <i class="ph ph-users" style="font-size:48px;opacity:0.4;display:block;margin-bottom:12px"></i>
            <p>Tidak ada pengguna ditemukan</p>
        </div>
    @else
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Nama / Email</th>
                    <th>NIP</th>
                    <th>Departemen</th>
                    <th>Peran</th>
                    <th>Status</th>
                    <th>Login Terakhir</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px">
                            <img src="{{ $user->avatar_url }}" style="width:36px;height:36px;border-radius:50%;object-fit:cover" onerror="this.style.display='none'">
                            <div>
                                <div style="font-size:13px;font-weight:600">{{ $user->name }}</div>
                                <div style="font-size:12px;color:var(--text-muted)">{{ $user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="text-sm">{{ $user->nip ?: '—' }}</td>
                    <td class="text-sm">{{ $user->department ?: '—' }}</td>
                    <td>
                        @php
                            $roleColors = ['admin'=>'accent','manager'=>'info','staff'=>'gray'];
                            $rc = $roleColors[$user->role] ?? 'gray';
                        @endphp
                        <span class="badge badge-{{ $rc }}">{{ $user->role_label }}</span>
                    </td>
                    <td>
                        <!-- Toggle Active Switch -->
                        <form method="POST" action="{{ route('administrasi.users.toggle-active', $user) }}">
                            @csrf
                            <label class="toggle-switch" title="{{ $user->is_active ? 'Klik untuk nonaktifkan' : 'Klik untuk aktifkan' }}">
                                <input type="checkbox" {{ $user->is_active ? 'checked' : '' }} onchange="this.form.submit()">
                                <span class="toggle-slider"></span>
                            </label>
                        </form>
                    </td>
                    <td class="text-sm text-muted">
                        {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Belum pernah' }}
                    </td>
                    <td>
                        <div class="flex gap-2">
                            <a href="{{ route('administrasi.users.show', $user) }}" class="btn btn-secondary btn-sm btn-icon" title="Detail">
                                <i class="ph ph-eye"></i>
                            </a>
                            <a href="{{ route('administrasi.users.edit', $user) }}" class="btn btn-secondary btn-sm btn-icon" title="Edit">
                                <i class="ph ph-pencil"></i>
                            </a>
                            @if($user->id !== auth()->id())
                            <form method="POST" action="{{ route('administrasi.users.destroy', $user) }}" onsubmit="return confirm('Hapus pengguna {{ $user->name }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Hapus">
                                    <i class="ph ph-trash"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="pagination-wrapper">
        <span class="text-sm text-muted">Menampilkan {{ $users->firstItem() }}–{{ $users->lastItem() }} dari {{ $users->total() }}</span>
        <div class="pagination">
            @if(!$users->onFirstPage())
                <a href="{{ $users->previousPageUrl() }}" class="page-btn">‹</a>
            @endif
            @foreach($users->getUrlRange(max(1,$users->currentPage()-2),min($users->lastPage(),$users->currentPage()+2)) as $page => $url)
                <a href="{{ $url }}" class="page-btn {{ $page==$users->currentPage()?'active':'' }}">{{ $page }}</a>
            @endforeach
            @if($users->hasMorePages())
                <a href="{{ $users->nextPageUrl() }}" class="page-btn">›</a>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection
