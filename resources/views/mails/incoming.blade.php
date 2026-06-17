@extends('layouts.app')
@section('title', $title ?? 'Daftar Surat')
@section('page-title', $title ?? 'Daftar Surat')

@php
    $typeRouteMap = [
        'incoming' => ['create'=>'mails.incoming.create','index'=>'mails.incoming.index'],
        'outgoing' => ['create'=>'mails.outgoing.create','index'=>'mails.outgoing.index'],
        'internal' => ['create'=>'mails.internal.create','index'=>'mails.internal.index'],
    ];
    $mailType = $mails->first()?->type ?? request()->segment(1);
    // Determine type from route
    $currentType = 'incoming';
    if(str_contains(request()->path(),'keluar')) $currentType = 'outgoing';
    elseif(str_contains(request()->path(),'internal')) $currentType = 'internal';
@endphp

@section('header-actions')
    <a href="{{ route('mails.import.index') }}" class="btn btn-secondary" style="margin-right: 8px;">
        <i class="ph ph-file-arrow-up"></i> Import Data
    </a>
    <a href="{{ route($typeRouteMap[$currentType]['create']) }}" class="btn btn-primary">
        <i class="ph ph-plus"></i> Tambah Surat
    </a>
@endsection

@section('content')
<div class="card">
    <!-- Filter Bar -->
    <form method="GET" id="filterForm">
        <div class="filter-bar">
            <div class="search-wrap">
                <i class="ph ph-magnifying-glass"></i>
                <input type="text" name="search" class="form-control" placeholder="Cari perihal, nomor, pengirim..." value="{{ request('search') }}">
            </div>
            <select name="status" class="form-control" onchange="document.getElementById('filterForm').submit()">
                <option value="">Semua Status</option>
                <option value="pending" {{ request('status')=='pending' ? 'selected' : '' }}>Pending</option>
                <option value="in_progress" {{ request('status')=='in_progress' ? 'selected' : '' }}>Diproses</option>
                <option value="completed" {{ request('status')=='completed' ? 'selected' : '' }}>Selesai</option>
                <option value="archived" {{ request('status')=='archived' ? 'selected' : '' }}>Diarsipkan</option>
            </select>
            <select name="priority" class="form-control" onchange="document.getElementById('filterForm').submit()">
                <option value="">Semua Prioritas</option>
                <option value="normal" {{ request('priority')=='normal' ? 'selected' : '' }}>Biasa</option>
                <option value="urgent" {{ request('priority')=='urgent' ? 'selected' : '' }}>Mendesak</option>
                <option value="very_urgent" {{ request('priority')=='very_urgent' ? 'selected' : '' }}>Sangat Mendesak</option>
            </select>
            <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}" title="Dari tanggal">
            <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}" title="Sampai tanggal">
            <button type="submit" class="btn btn-secondary"><i class="ph ph-funnel"></i> Filter</button>
            @if(request()->hasAny(['search','status','priority','from_date','to_date']))
                <a href="{{ url()->current() }}" class="btn btn-secondary"><i class="ph ph-x"></i> Reset</a>
            @endif
        </div>
    </form>

    @if($mails->isEmpty())
        <div style="text-align:center;padding:50px 0;color:var(--text-muted)">
            <i class="ph ph-envelope" style="font-size:48px;opacity:0.4;display:block;margin-bottom:12px"></i>
            <p>Belum ada surat ditemukan</p>
        </div>
    @else
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>No. Referensi</th>
                    <th>Perihal</th>
                    @if($currentType === 'incoming')
                        <th>Pengirim</th>
                    @else
                        <th>Penerima</th>
                    @endif
                    <th>Prioritas</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($mails as $mail)
                <tr>
                    <td>
                        <a href="{{ route('mails.show', $mail) }}" class="text-accent font-medium" style="text-decoration:none;font-size:13px">
                            {{ $mail->reference_number }}
                        </a>
                    </td>
                    <td style="max-width:280px">
                        <span style="display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-size:13px" title="{{ $mail->subject }}">
                            {{ $mail->subject }}
                        </span>
                        @if($mail->classification !== 'open')
                            <span class="badge badge-warning text-xs" style="margin-top:3px">{{ ucfirst($mail->classification) }}</span>
                        @endif
                    </td>
                    <td style="font-size:13px">
                        @if($currentType === 'incoming')
                            {{ $mail->sender_name }}
                            @if($mail->sender_organization)
                                <div class="text-xs text-muted">{{ $mail->sender_organization }}</div>
                            @endif
                        @else
                            {{ $mail->recipient_name }}
                            @if($mail->recipient_department)
                                <div class="text-xs text-muted">{{ $mail->recipient_department }}</div>
                            @endif
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-{{ $mail->priority_color }}">{{ $mail->priority_label }}</span>
                    </td>
                    <td>
                        <span class="badge badge-{{ $mail->status_color }}">{{ $mail->status_label }}</span>
                    </td>
                    <td class="text-sm text-muted">{{ $mail->tanggal_surat->format('d/m/Y') }}</td>
                    <td>
                        <div class="flex gap-2">
                            <a href="{{ route('mails.show', $mail) }}" class="btn btn-secondary btn-sm btn-icon" title="Lihat Detail">
                                <i class="ph ph-eye"></i>
                            </a>
                            <a href="{{ route('mails.edit', $mail) }}" class="btn btn-secondary btn-sm btn-icon" title="Edit">
                                <i class="ph ph-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('mails.destroy', $mail) }}" onsubmit="return confirm('Hapus surat ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Hapus">
                                    <i class="ph ph-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="pagination-wrapper">
        <span class="text-sm text-muted">
            Menampilkan {{ $mails->firstItem() }}–{{ $mails->lastItem() }} dari {{ $mails->total() }} surat
        </span>
        <div class="pagination">
            @if($mails->onFirstPage())
                <span class="page-btn" style="opacity:0.4">‹</span>
            @else
                <a href="{{ $mails->previousPageUrl() }}" class="page-btn">‹</a>
            @endif

            @foreach($mails->getUrlRange(max(1,$mails->currentPage()-2), min($mails->lastPage(),$mails->currentPage()+2)) as $page => $url)
                <a href="{{ $url }}" class="page-btn {{ $page == $mails->currentPage() ? 'active' : '' }}">{{ $page }}</a>
            @endforeach

            @if($mails->hasMorePages())
                <a href="{{ $mails->nextPageUrl() }}" class="page-btn">›</a>
            @else
                <span class="page-btn" style="opacity:0.4">›</span>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection
