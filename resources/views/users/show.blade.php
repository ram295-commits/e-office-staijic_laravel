@extends('layouts.app')
@section('title', 'Profil — ' . $user->name)
@section('page-title', 'Profil Pengguna')

@section('header-actions')
    <a href="{{ route('administrasi.users.edit', $user) }}" class="btn btn-secondary btn-sm"><i class="ph ph-pencil"></i> Edit</a>
    @if($user->id !== auth()->id())
    <form method="POST" action="{{ route('administrasi.users.toggle-active', $user) }}" style="display:inline">
        @csrf
        <button type="submit" class="btn {{ $user->is_active ? 'btn-warning' : 'btn-success' }} btn-sm">
            <i class="ph ph-{{ $user->is_active ? 'prohibit' : 'check-circle' }}"></i>
            {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
        </button>
    </form>
    @endif
@endsection

@section('content')
<div class="breadcrumb">
    <a href="{{ route('administrasi.users.index') }}">Kelola Pengguna</a>
    <i class="ph ph-caret-right" style="font-size:12px"></i>
    <span>{{ $user->name }}</span>
</div>

<div style="display:grid;grid-template-columns:300px 1fr;gap:20px;align-items:start">
    <!-- Profile card -->
    <div class="card" style="text-align:center">
        <img src="{{ $user->avatar_url }}" style="width:80px;height:80px;border-radius:50%;margin:0 auto 12px;display:block;object-fit:cover" onerror="this.style.display='none'">
        <h2 style="font-size:18px;font-weight:700;margin-bottom:4px">{{ $user->name }}</h2>
        <p class="text-muted text-sm">{{ $user->position ?: $user->role_label }}</p>
        @if($user->department)
            <p class="text-sm" style="margin-top:4px;color:var(--accent)">{{ $user->department }}</p>
        @endif

        <hr class="divider">

        <div style="text-align:left;display:grid;gap:12px">
            <div>
                <div style="font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em">Email</div>
                <div style="font-size:13px;margin-top:3px">{{ $user->email }}</div>
            </div>
            @if($user->nip)
            <div>
                <div style="font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em">NIP</div>
                <div style="font-size:13px;margin-top:3px;font-family:monospace">{{ $user->nip }}</div>
            </div>
            @endif
            <div>
                <div style="font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em">Peran</div>
                <div style="margin-top:5px">
                    @php $rc = ['admin'=>'accent','manager'=>'info','staff'=>'gray'][$user->role] ?? 'gray'; @endphp
                    <span class="badge badge-{{ $rc }}">{{ $user->role_label }}</span>
                </div>
            </div>
            <div>
                <div style="font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em">Status</div>
                <div style="margin-top:5px">
                    <span class="badge {{ $user->is_active ? 'badge-success' : 'badge-danger' }}">
                        {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
            </div>
            <div>
                <div style="font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em">Login Terakhir</div>
                <div style="font-size:13px;margin-top:3px">
                    {{ $user->last_login_at ? $user->last_login_at->format('d M Y, H:i') : 'Belum pernah' }}
                </div>
            </div>
            <div>
                <div style="font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em">Bergabung</div>
                <div style="font-size:13px;margin-top:3px">{{ $user->created_at->format('d M Y') }}</div>
            </div>
        </div>
    </div>

    <!-- Stats & Activity -->
    <div>
        <div class="stat-grid" style="margin-bottom:16px">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="ph ph-envelope-simple"></i></div>
                <div>
                    <div class="stat-value">{{ $stats['created_mails'] }}</div>
                    <div class="stat-label">Surat Dibuat</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="ph ph-user-check"></i></div>
                <div>
                    <div class="stat-value">{{ $stats['assigned_mails'] }}</div>
                    <div class="stat-label">Surat Ditugaskan</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon yellow"><i class="ph ph-git-branch"></i></div>
                <div>
                    <div class="stat-value">{{ $stats['received_disp'] }}</div>
                    <div class="stat-label">Total Disposisi</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon red"><i class="ph ph-clock"></i></div>
                <div>
                    <div class="stat-value">{{ $stats['pending_disp'] }}</div>
                    <div class="stat-label">Disposisi Pending</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
