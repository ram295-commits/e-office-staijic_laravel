@extends('layouts.app')
@section('title', 'Arsip Surat')
@section('page-title', 'Arsip Surat')

@section('header-actions')
    <a href="{{ route('mails.archive.export', request()->query()) }}" class="btn btn-secondary bg-white hover:bg-gray-50 text-gray-700 font-medium py-2 px-4 rounded-md border border-gray-200 shadow-sm flex items-center gap-2 transition duration-200">
        <i class="ph ph-file-csv text-lg"></i> Export CSV
    </a>
    <a href="{{ route('mails.archive.export-pdf', request()->query()) }}" class="btn btn-primary bg-primary hover:bg-emerald-700 text-white font-medium py-2 px-4 rounded-md shadow-sm flex items-center gap-2 transition duration-200 ml-2">
        <i class="ph ph-file-pdf text-lg"></i> Export PDF
    </a>
@endsection

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    
    <!-- Filter Bar -->
    <form method="GET" id="filterForm" class="mb-6 bg-gray-50 p-4 rounded-lg border border-gray-100 flex flex-wrap items-end gap-4">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Cari Surat</label>
            <div class="relative">
                <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text" name="search" class="w-full pl-9 pr-3 py-2 bg-white border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-secondary focus:border-transparent outline-none transition" placeholder="No. ref atau perihal..." value="{{ request('search') }}">
            </div>
        </div>

        <div class="w-full sm:w-auto">
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Tipe</label>
            <select name="type" class="w-full sm:w-40 px-3 py-2 bg-white border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-secondary focus:border-transparent outline-none transition" onchange="this.form.submit()">
                <option value="">Semua Tipe</option>
                <option value="incoming" {{ request('type')=='incoming' ? 'selected' : '' }}>Surat Masuk</option>
                <option value="outgoing" {{ request('type')=='outgoing' ? 'selected' : '' }}>Surat Keluar</option>
                <option value="internal" {{ request('type')=='internal' ? 'selected' : '' }}>Surat Internal</option>
            </select>
        </div>

        <div class="w-full sm:w-auto">
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Tahun</label>
            <select name="year" class="w-full sm:w-32 px-3 py-2 bg-white border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-secondary focus:border-transparent outline-none transition" onchange="this.form.submit()">
                <option value="">Semua</option>
                @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                    <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>

        <div class="w-full sm:w-auto">
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Klasifikasi</label>
            <select name="classification" class="w-full sm:w-40 px-3 py-2 bg-white border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-secondary focus:border-transparent outline-none transition" onchange="this.form.submit()">
                <option value="">Semua</option>
                <option value="open" {{ request('classification')=='open' ? 'selected' : '' }}>Terbuka</option>
                <option value="confidential" {{ request('classification')=='confidential' ? 'selected' : '' }}>Rahasia</option>
                <option value="secret" {{ request('classification')=='secret' ? 'selected' : '' }}>Sangat Rahasia</option>
            </select>
        </div>

        <div class="flex items-center gap-2">
            <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-md text-sm font-medium transition shadow-sm">
                Filter
            </button>
            @if(request()->hasAny(['search','type','year','month','classification']))
                <a href="{{ route('mails.archive.index') }}" class="bg-white border border-gray-300 text-gray-600 hover:bg-gray-50 px-4 py-2 rounded-md text-sm font-medium transition">
                    Reset
                </a>
            @endif
        </div>
    </form>

    @if($mails->isEmpty())
        <!-- Empty State -->
        <div class="py-16 px-6 text-center bg-gray-50/50 rounded-lg border border-dashed border-gray-200">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-white shadow-sm border border-gray-100 mb-4 text-gray-400">
                <i class="ph ph-archive-box text-3xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-1">Arsip Kosong</h3>
            <p class="text-gray-500 text-sm max-w-md mx-auto">Belum ada surat yang diarsipkan, atau tidak ada surat yang sesuai dengan kriteria filter pencarian Anda.</p>
        </div>
    @else
        <!-- Data Table -->
        <div class="overflow-x-auto border border-gray-100 rounded-lg">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr>
                        <th class="px-5 py-3 bg-gray-50 border-b border-gray-100 text-xs font-bold text-gray-500 uppercase tracking-wider">No. Referensi</th>
                        <th class="px-5 py-3 bg-gray-50 border-b border-gray-100 text-xs font-bold text-gray-500 uppercase tracking-wider">Tipe</th>
                        <th class="px-5 py-3 bg-gray-50 border-b border-gray-100 text-xs font-bold text-gray-500 uppercase tracking-wider">Perihal</th>
                        <th class="px-5 py-3 bg-gray-50 border-b border-gray-100 text-xs font-bold text-gray-500 uppercase tracking-wider">Pihak Terkait</th>
                        <th class="px-5 py-3 bg-gray-50 border-b border-gray-100 text-xs font-bold text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-5 py-3 bg-gray-50 border-b border-gray-100 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($mails as $mail)
                    <tr class="hover:bg-gray-50/80 transition-colors">
                        <td class="px-5 py-4">
                            <a href="{{ route('mails.show', $mail) }}" class="text-sm font-bold text-primary hover:text-secondary transition-colors">
                                {{ $mail->reference_number }}
                            </a>
                        </td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700 border border-gray-200">
                                {{ $mail->type_label }}
                            </span>
                            @if($mail->is_backdated)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200 cursor-help ml-1" title="Tanggal Surat: {{ $mail->tanggal_surat->format('d-m-Y') }} | Di-input Sistem: {{ $mail->created_at->format('d-m-Y H:i') }}">
                                    <i class="ph ph-clock-counter-clockwise mr-1 text-xs"></i> Backdate
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-4 max-w-xs">
                            <div class="text-sm text-gray-800 truncate font-medium" title="{{ $mail->subject }}">
                                {{ $mail->subject }}
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="text-xs text-gray-500">
                                @if($mail->type === 'incoming')
                                    Dari: <span class="text-gray-800 font-medium">{{ $mail->sender_name }}</span>
                                @else
                                    Ke: <span class="text-gray-800 font-medium">{{ $mail->recipient_name }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-4 text-sm text-gray-600">
                            {{ $mail->tanggal_surat->format('d M Y') }}
                        </td>
                        <td class="px-5 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('mails.show', $mail) }}" class="p-1.5 text-gray-400 hover:text-primary bg-white border border-gray-200 rounded hover:bg-gray-50 transition" title="Lihat Detail">
                                    <i class="ph ph-eye text-lg"></i>
                                </a>
                                @if(auth()->user()->isAdmin() || auth()->user()->isManager())
                                <a href="{{ route('administrasi.nomor-surat.revisi-form', $mail->id) }}" class="p-1.5 text-gray-400 hover:text-warning bg-white border border-gray-200 rounded hover:bg-gray-50 transition" title="Revisi Nomor">
                                    <i class="ph ph-arrows-clockwise text-lg"></i>
                                </a>
                                @endif
                                @if(auth()->user()->isManager())
                                <form method="POST" action="{{ route('mails.status', $mail) }}" onsubmit="return confirm('Kembalikan surat ini ke status Selesai?')">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="completed">
                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-secondary bg-white border border-gray-200 rounded hover:bg-gray-50 transition" title="Restore (Kembalikan ke Selesai)">
                                        <i class="ph ph-arrow-counter-clockwise text-lg"></i>
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

        <!-- Pagination -->
        <div class="mt-6 border-t border-gray-100 pt-4 flex items-center justify-between">
            <div class="text-sm text-gray-500">
                Menampilkan <span class="font-medium text-gray-900">{{ $mails->firstItem() }}</span> sampai <span class="font-medium text-gray-900">{{ $mails->lastItem() }}</span> dari <span class="font-medium text-gray-900">{{ $mails->total() }}</span> hasil
            </div>
            <div>
                {{ $mails->appends(request()->query())->links('pagination::tailwind') }}
            </div>
        </div>
    @endif
</div>
@endsection
