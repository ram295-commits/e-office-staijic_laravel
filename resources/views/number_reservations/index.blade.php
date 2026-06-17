@extends('layouts.app')
@section('title', 'Reservasi Nomor Surat')
@section('page-title', 'Reservasi Nomor Surat')

@section('header-actions')
    @can('create', App\Models\NumberReservation::class)
    <a href="{{ route('number_reservations.create') }}" class="btn btn-primary">
        <i class="ph ph-plus-circle"></i> Ajukan Reservasi
    </a>
    @endcan
@endsection

@section('content')
<div class="breadcrumb mb-6">
    <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-primary transition-colors">Dashboard</a>
    <i class="ph ph-caret-right text-gray-400" style="font-size:12px; margin: 0 8px;"></i>
    <span class="text-gray-800 font-medium">Reservasi Nomor</span>
</div>

@php
    $countPending = \App\Models\NumberReservation::where('status', 'pending')->count();
    $countApproved = \App\Models\NumberReservation::where('status', 'approved')->count();
    $countRejected = \App\Models\NumberReservation::where('status', 'rejected')->count();
    $countAll = \App\Models\NumberReservation::count();
@endphp

<!-- Filter Tabs -->
<div class="flex gap-2 border-b border-gray-200 mb-6 overflow-x-auto">
    <a href="{{ route('number_reservations.index', ['status' => 'pending']) }}" class="px-4 py-2 text-sm font-semibold border-b-2 transition-colors flex items-center gap-2 {{ $status === 'pending' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
        Pending 
        <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $status === 'pending' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600' }}">{{ $countPending }}</span>
    </a>
    <a href="{{ route('number_reservations.index', ['status' => 'approved']) }}" class="px-4 py-2 text-sm font-semibold border-b-2 transition-colors flex items-center gap-2 {{ $status === 'approved' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
        Disetujui 
        <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $status === 'approved' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600' }}">{{ $countApproved }}</span>
    </a>
    <a href="{{ route('number_reservations.index', ['status' => 'rejected']) }}" class="px-4 py-2 text-sm font-semibold border-b-2 transition-colors flex items-center gap-2 {{ $status === 'rejected' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
        Ditolak 
        <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $status === 'rejected' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600' }}">{{ $countRejected }}</span>
    </a>
    <a href="{{ route('number_reservations.index', ['status' => 'all']) }}" class="px-4 py-2 text-sm font-semibold border-b-2 transition-colors flex items-center gap-2 {{ $status === 'all' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
        Semua 
        <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $status === 'all' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600' }}">{{ $countAll }}</span>
    </a>
</div>

<div class="card p-0 overflow-hidden">
    <div class="table-wrapper">
        <table class="w-full">
            <thead>
                <tr>
                    <th class="w-24">ID</th>
                    <th>Pemohon</th>
                    <th>Detail Dokumen</th>
                    <th class="w-32">Jumlah Slot</th>
                    <th>Tanggal Target</th>
                    <th>Keterangan / Status</th>
                    <th class="w-48 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reservations as $reservation)
                    <tr x-data="{ showReject: false }">
                        <td class="align-top font-bold text-gray-500">
                            #{{ $reservation->id }}
                        </td>
                        <td class="align-top">
                            <div class="font-bold text-gray-800">{{ $reservation->requester->name }}</div>
                            <div class="text-xs text-gray-500">{{ $reservation->requester->department ?? 'Unit Kerja' }}</div>
                        </td>
                        <td class="align-top">
                            <div class="font-semibold text-gray-700">{{ $reservation->documentType->name }}</div>
                            <div class="text-xs text-gray-500">Format: <code class="bg-gray-100 px-1 py-0.5 rounded">{{ $reservation->letterFormat->format_string }}</code></div>
                        </td>
                        <td class="align-top font-semibold text-gray-800">
                            {{ $reservation->quantity }} Nomor
                        </td>
                        <td class="align-top">
                            <div class="font-semibold text-gray-800">{{ $reservation->backdate_target->format('d M Y') }}</div>
                            <div class="text-xs text-red-500 font-medium">Backdated</div>
                        </td>
                        <td class="align-top">
                            <div class="text-sm text-gray-600 italic">"{{ $reservation->reason }}"</div>
                            
                            @if($reservation->status === 'approved')
                                <span class="badge badge-success mt-2">Disetujui</span>
                                @if($reservation->approver)
                                    <div class="text-[10px] text-gray-500 mt-1">Oleh: {{ $reservation->approver->name }}</div>
                                @endif
                            @elseif($reservation->status === 'rejected')
                                <span class="badge badge-danger mt-2">Ditolak</span>
                                @if($reservation->approver)
                                    <div class="text-[10px] text-gray-500 mt-1">Oleh: {{ $reservation->approver->name }}</div>
                                @endif
                                <div class="text-xs text-red-600 mt-1 font-medium bg-red-50 border border-red-100 rounded p-2">
                                    <strong>Alasan Penolakan:</strong> {{ $reservation->reason }}
                                </div>
                            @else
                                <span class="badge badge-warning mt-2">Pending</span>
                            @endif
                        </td>
                        <td class="align-top text-right">
                            <div class="flex items-center justify-end gap-2">
                                @can('approve', $reservation)
                                    @if($reservation->status === 'pending')
                                        <form method="POST" action="{{ route('number_reservations.approve', $reservation->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menyetujui reservasi nomor ini? Tindakan ini akan membuat draft surat dengan nomor urut yang terkunci.')">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm">
                                                <i class="ph ph-check"></i> Setujui
                                            </button>
                                        </form>
                                        <button type="button" @click="showReject = !showReject" class="btn btn-danger btn-sm">
                                            <i class="ph ph-x"></i> Tolak
                                        </button>
                                    @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Conditional row for rejection reason input -->
                    @can('approve', $reservation)
                        @if($reservation->status === 'pending')
                            <tr x-show="showReject" x-transition x-cloak>
                                <td colspan="7" class="bg-red-50/50 border-b border-gray-100 px-6 py-4">
                                    <form method="POST" action="{{ route('number_reservations.reject', $reservation->id) }}" class="flex flex-col sm:flex-row gap-3 items-end">
                                        @csrf
                                        <div class="flex-1 text-left">
                                            <label class="form-label text-xs font-bold text-red-700">Alasan Penolakan <span class="req">*</span></label>
                                            <input type="text" name="rejection_reason" class="form-control text-sm" placeholder="Contoh: Tanggal backdate melanggar kronologi arsip fisik." required>
                                        </div>
                                        <div class="flex gap-2">
                                            <button type="button" @click="showReject = false" class="btn btn-secondary btn-sm">Batal</button>
                                            <button type="submit" class="btn btn-danger btn-sm">Konfirmasi Tolak</button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @endif
                    @endcan

                    <!-- Conditional row for slot list if approved -->
                    @if($reservation->status === 'approved')
                        <tr>
                            <td colspan="7" class="bg-gray-50/50 border-b border-gray-100 px-6 py-3">
                                <div class="bg-white rounded-lg border border-gray-150 p-4 shadow-inner-sm">
                                    <div class="text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-3 flex items-center gap-1.5">
                                        <i class="ph ph-list-numbers text-base text-primary"></i> 
                                        Slot Nomor Terkunci untuk Reservasi ini:
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                        @forelse($reservation->mails as $mail)
                                            <div class="bg-gray-50/80 p-3 rounded-lg border border-gray-200 flex items-center justify-between transition hover:bg-gray-50">
                                                <div>
                                                    <div class="font-bold text-gray-800 text-sm flex items-center gap-1">
                                                        <i class="ph ph-hash text-gray-400"></i>
                                                        {{ $mail->reference_number }}
                                                    </div>
                                                    <div class="text-xs text-gray-500 mt-0.5">
                                                        {{ $mail->tanggal_surat->format('d M Y') }}
                                                    </div>
                                                    <div class="mt-1.5">
                                                        @if($mail->status === 'draft')
                                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Draft (Kosong)
                                                            </span>
                                                        @else
                                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-800 border border-green-200">
                                                                <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Terisi ({{ ucfirst($mail->status) }})
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div>
                                                    @if($mail->status === 'draft')
                                                        @can('fillSlot', $reservation)
                                                            <a href="{{ route('number_reservations.fill_slot.show', ['reservation' => $reservation->id, 'mail' => $mail->id]) }}" class="btn btn-primary btn-sm px-2.5 py-1.5 text-xs">
                                                                <i class="ph ph-pencil-line"></i> Isi Slot
                                                            </a>
                                                        @endcan
                                                    @else
                                                        <a href="{{ route('mails.view', $mail->id) }}" class="btn btn-secondary btn-sm px-2.5 py-1.5 text-xs text-primary hover:text-primary">
                                                            <i class="ph ph-eye"></i> Detail
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        @empty
                                            <div class="col-span-full text-center py-4 text-sm text-gray-500">
                                                Tidak ada slot surat ditemukan untuk reservasi ini.
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-12 text-gray-500">
                            <i class="ph ph-hash-straight text-4xl text-gray-300 mb-2"></i>
                            <p class="text-sm">Tidak ada permintaan reservasi nomor dalam status ini.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">
    {{ $reservations->links() }}
</div>
@endsection
