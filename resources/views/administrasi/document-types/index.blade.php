@extends('layouts.app')
@section('title', 'Kelola Jenis Surat')
@section('page-title', 'Kelola Jenis Surat')

@section('header-actions')
    <a href="{{ route('administrasi.document-types.create') }}" class="btn btn-primary"><i class="ph ph-plus"></i> Tambah Jenis Surat</a>
@endsection

@section('content')
<div class="card">
    @if($documentTypes->isEmpty())
        <div style="text-align:center;padding:50px 0;color:var(--text-muted)">
            <i class="ph ph-files" style="font-size:48px;opacity:0.4;display:block;margin-bottom:12px"></i>
            <p>Belum ada data jenis surat.</p>
        </div>
    @else
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Nama Jenis Surat</th>
                    <th>Kode</th>
                    <th>Unit Terkait</th>
                    <th>Deskripsi</th>
                    <th style="width: 120px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($documentTypes as $index => $type)
                <tr>
                    <td class="text-sm text-center">{{ $documentTypes->firstItem() + $index }}</td>
                    <td style="font-weight: 500;">{{ $type->name }}</td>
                    <td><span class="badge badge-accent">{{ $type->code }}</span></td>
                    <td class="text-sm">{{ $type->unit->name ?? '—' }}</td>
                    <td class="text-sm text-muted">{{ Str::limit($type->description, 50) ?: '—' }}</td>
                    <td>
                        <div class="flex gap-2 justify-center">
                            <a href="{{ route('administrasi.document-types.edit', $type) }}" class="btn btn-secondary btn-sm btn-icon" title="Edit">
                                <i class="ph ph-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('administrasi.document-types.destroy', $type) }}" onsubmit="return confirm('Hapus jenis surat {{ $type->name }}? Data tidak dapat dipulihkan.')">
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

    @if($documentTypes->hasPages())
    <div class="pagination-wrapper mt-4">
        <span class="text-sm text-muted">Menampilkan {{ $documentTypes->firstItem() }}–{{ $documentTypes->lastItem() }} dari {{ $documentTypes->total() }}</span>
        <div class="pagination">
            @if(!$documentTypes->onFirstPage())
                <a href="{{ $documentTypes->previousPageUrl() }}" class="page-btn">‹</a>
            @endif
            @foreach($documentTypes->getUrlRange(max(1,$documentTypes->currentPage()-2),min($documentTypes->lastPage(),$documentTypes->currentPage()+2)) as $page => $url)
                <a href="{{ $url }}" class="page-btn {{ $page==$documentTypes->currentPage()?'active':'' }}">{{ $page }}</a>
            @endforeach
            @if($documentTypes->hasMorePages())
                <a href="{{ $documentTypes->nextPageUrl() }}" class="page-btn">›</a>
            @endif
        </div>
    </div>
    @endif
    @endif
</div>
@endsection
