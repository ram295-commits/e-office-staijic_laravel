@extends('layouts.app')
@section('title', 'Kelola Jenis Surat')
@section('page-title', 'Kelola Jenis Surat')

@section('header-actions')
<div class="btn-group">
    {{-- Export Button --}}
    <a href="{{ route('administrasi.document-types.export') }}"
       class="btn btn-secondary btn-sm"
       title="Export ke Excel">
        <i class="ph ph-download-simple"></i>
        <span class="hidden sm:inline">Export</span>
    </a>

    {{-- Import Button (opens modal) --}}
    <button type="button" onclick="openImportModal()"
            class="btn btn-secondary btn-sm"
            title="Import dari Excel/CSV">
        <i class="ph ph-upload-simple"></i>
        <span class="hidden sm:inline">Import</span>
    </button>

    {{-- Add New --}}
    <a href="{{ route('administrasi.document-types.create') }}" class="btn btn-primary btn-sm">
        <i class="ph ph-plus"></i>
        <span class="hidden sm:inline">Tambah Jenis Surat</span>
        <span class="sm:hidden">Tambah</span>
    </a>
</div>
@endsection

@section('content')

{{-- ── Import Modal ──────────────────────────────────────── --}}
<div id="importModal" class="modal-backdrop hidden" onclick="closeImportModal(event)">
    <div class="modal-box" onclick="event.stopPropagation()">
        <button onclick="closeImportModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition">
            <i class="ph ph-x text-xl"></i>
        </button>

        <h3 class="text-lg font-bold text-gray-800 mb-1 flex items-center gap-2">
            <i class="ph ph-upload-simple text-secondary"></i>
            Import Jenis Surat
        </h3>
        <p class="text-sm text-gray-500 mb-5">
            Upload file <strong>.xlsx</strong>, <strong>.xls</strong>, atau <strong>.csv</strong>.
            Kode yang sudah ada akan dilewati.
        </p>

        {{-- Column reference --}}
        <div class="bg-gray-50 rounded-xl p-3 mb-5 text-xs text-gray-600 space-y-1 border border-gray-100">
            <p class="font-bold text-gray-700 mb-2 uppercase tracking-wide text-[10px]">Kolom yang dibutuhkan (baris ke-1 = header):</p>
            <div class="grid grid-cols-2 gap-x-4 gap-y-1">
                <span>• <code class="bg-white px-1 rounded border border-gray-200">Nama Jenis Surat</code> <span class="text-red-500">*</span></span>
                <span>• <code class="bg-white px-1 rounded border border-gray-200">Kode</code> <span class="text-red-500">*</span></span>
                <span>• <code class="bg-white px-1 rounded border border-gray-200">Unit Terkait</code></span>
                <span>• <code class="bg-white px-1 rounded border border-gray-200">Deskripsi</code></span>
            </div>
            <p class="mt-2 text-gray-400">💡 Export data yang ada untuk melihat format yang benar.</p>
        </div>

        <form method="POST" action="{{ route('administrasi.document-types.import') }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-4">
                <label for="import_file" class="form-label">Pilih File <span class="req">*</span></label>
                <input type="file" name="file" id="import_file" accept=".xlsx,.xls,.csv"
                       class="form-control text-sm" required>
            </div>
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="closeImportModal()" class="btn btn-secondary btn-sm">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="ph ph-upload-simple"></i> Upload & Import
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── Main Card ──────────────────────────────────────────── --}}
<div class="card">

    @if($documentTypes->isEmpty())
        <div class="flex flex-col items-center justify-center py-16 text-gray-400">
            <i class="ph ph-files text-5xl opacity-30 mb-3"></i>
            <p class="text-sm font-medium">Belum ada data jenis surat.</p>
            <a href="{{ route('administrasi.document-types.create') }}" class="btn btn-primary btn-sm mt-4">
                <i class="ph ph-plus"></i> Tambah Sekarang
            </a>
        </div>
    @else

    {{-- ── Sort Helper ──────────────────────────────────────── --}}
    @php
        /**
         * Build a sort URL toggling direction when the same column is re-clicked.
         */
        function sortUrl(string $col, string $currentSort, string $currentDir): string {
            $dir = ($currentSort === $col && $currentDir === 'asc') ? 'desc' : 'asc';
            return request()->fullUrlWithQuery(['sort' => $col, 'direction' => $dir, 'page' => 1]);
        }
        function sortIcon(string $col, string $currentSort, string $currentDir): string {
            if ($currentSort !== $col) return '<span class="sort-icon">⇅</span>';
            return $currentDir === 'asc'
                ? '<span class="sort-icon active">▲</span>'
                : '<span class="sort-icon active">▼</span>';
        }
    @endphp

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>

                    <th class="th-sortable">
                        <a href="{{ sortUrl('name', $sort, $direction) }}" class="flex items-center gap-1 no-underline text-inherit">
                            Nama Jenis Surat {!! sortIcon('name', $sort, $direction) !!}
                        </a>
                    </th>

                    <th class="th-sortable">
                        <a href="{{ sortUrl('code', $sort, $direction) }}" class="flex items-center gap-1 no-underline text-inherit">
                            Kode {!! sortIcon('code', $sort, $direction) !!}
                        </a>
                    </th>

                    <th class="th-sortable">
                        <a href="{{ sortUrl('unit', $sort, $direction) }}" class="flex items-center gap-1 no-underline text-inherit">
                            Unit Terkait {!! sortIcon('unit', $sort, $direction) !!}
                        </a>
                    </th>

                    <th class="th-sortable">
                        <a href="{{ sortUrl('description', $sort, $direction) }}" class="flex items-center gap-1 no-underline text-inherit">
                            Deskripsi {!! sortIcon('description', $sort, $direction) !!}
                        </a>
                    </th>

                    <th style="width: 100px;" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($documentTypes as $index => $type)
                <tr>
                    <td data-label="No" class="text-sm text-center text-gray-400 font-medium">
                        {{ $documentTypes->firstItem() + $index }}
                    </td>
                    <td data-label="Nama" style="font-weight: 500;">{{ $type->name }}</td>
                    <td data-label="Kode">
                        <span class="badge badge-accent font-mono tracking-wider">{{ $type->code }}</span>
                    </td>
                    <td data-label="Unit" class="text-sm">
                        @if($type->unit)
                            <span class="badge badge-info">{{ $type->unit->name }}</span>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td data-label="Deskripsi" class="text-sm text-gray-500">
                        {{ Str::limit($type->description, 60) ?: '—' }}
                    </td>
                    <td class="td-actions">
                        <div class="flex gap-2 justify-center">
                            <a href="{{ route('administrasi.document-types.edit', $type) }}"
                               class="btn btn-secondary btn-sm btn-icon" title="Edit">
                                <i class="ph ph-pencil"></i>
                            </a>
                            <form method="POST"
                                  action="{{ route('administrasi.document-types.destroy', $type) }}"
                                  onsubmit="return confirm('Hapus jenis surat \'{{ addslashes($type->name) }}\'?\nData tidak dapat dipulihkan.')">
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

    {{-- Pagination --}}
    @if($documentTypes->hasPages())
    <div class="pagination-wrapper mt-4">
        <span class="text-sm text-muted">
            Menampilkan {{ $documentTypes->firstItem() }}–{{ $documentTypes->lastItem() }}
            dari <strong>{{ $documentTypes->total() }}</strong> jenis surat
        </span>
        <div class="pagination">
            @if(!$documentTypes->onFirstPage())
                <a href="{{ $documentTypes->previousPageUrl() }}" class="page-btn" title="Sebelumnya">‹</a>
            @endif
            @foreach($documentTypes->getUrlRange(max(1,$documentTypes->currentPage()-2), min($documentTypes->lastPage(),$documentTypes->currentPage()+2)) as $page => $url)
                <a href="{{ $url }}" class="page-btn {{ $page == $documentTypes->currentPage() ? 'active' : '' }}">{{ $page }}</a>
            @endforeach
            @if($documentTypes->hasMorePages())
                <a href="{{ $documentTypes->nextPageUrl() }}" class="page-btn" title="Berikutnya">›</a>
            @endif
        </div>
    </div>
    @endif

    @endif
</div>
@endsection

@section('scripts')
<script>
    const importModal = document.getElementById('importModal');

    function openImportModal() {
        importModal.classList.remove('hidden');
        importModal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closeImportModal(event) {
        if (event && event.target !== importModal) return;
        importModal.classList.add('hidden');
        importModal.classList.remove('flex');
        document.body.style.overflow = '';
    }

    // Allow ESC to close the modal
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            importModal.classList.add('hidden');
            importModal.classList.remove('flex');
            document.body.style.overflow = '';
        }
    });

    // Auto-open import modal if there was a validation error on the import file field
    @if($errors->has('file'))
    openImportModal();
    @endif
</script>
@endsection
