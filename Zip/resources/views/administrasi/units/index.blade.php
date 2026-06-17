@extends('layouts.app')
@section('title', 'Kelola Unit')
@section('page-title', 'Kelola Unit')

@section('header-actions')
    <a href="{{ route('administrasi.units.create') }}" class="btn btn-primary"><i class="ph ph-plus"></i> Tambah Unit</a>
@endsection

@section('content')
<div class="card">
    @if($units->isEmpty())
        <div style="text-align:center;padding:50px 0;color:var(--text-muted)">
            <i class="ph ph-buildings" style="font-size:48px;opacity:0.4;display:block;margin-bottom:12px"></i>
            <p>Belum ada data unit.</p>
        </div>
    @else
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Nama Unit</th>
                    <th>Kode</th>
                    <th>Deskripsi</th>
                    <th style="width: 120px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($units as $index => $unit)
                <tr>
                    <td class="text-sm text-center">{{ $units->firstItem() + $index }}</td>
                    <td style="font-weight: 500;">{{ $unit->name }}</td>
                    <td><span class="badge badge-accent">{{ $unit->code }}</span></td>
                    <td class="text-sm text-muted">{{ Str::limit($unit->description, 50) ?: '—' }}</td>
                    <td>
                        <div class="flex gap-2 justify-center">
                            <a href="{{ route('administrasi.units.edit', $unit) }}" class="btn btn-secondary btn-sm btn-icon" title="Edit">
                                <i class="ph ph-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('administrasi.units.destroy', $unit) }}" onsubmit="return confirm('Hapus unit {{ $unit->name }}? Data tidak dapat dipulihkan.')">
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

    @if($units->hasPages())
    <div class="pagination-wrapper mt-4">
        <span class="text-sm text-muted">Menampilkan {{ $units->firstItem() }}–{{ $units->lastItem() }} dari {{ $units->total() }}</span>
        <div class="pagination">
            @if(!$units->onFirstPage())
                <a href="{{ $units->previousPageUrl() }}" class="page-btn">‹</a>
            @endif
            @foreach($units->getUrlRange(max(1,$units->currentPage()-2),min($units->lastPage(),$units->currentPage()+2)) as $page => $url)
                <a href="{{ $url }}" class="page-btn {{ $page==$units->currentPage()?'active':'' }}">{{ $page }}</a>
            @endforeach
            @if($units->hasMorePages())
                <a href="{{ $units->nextPageUrl() }}" class="page-btn">›</a>
            @endif
        </div>
    </div>
    @endif
    @endif
</div>
@endsection
