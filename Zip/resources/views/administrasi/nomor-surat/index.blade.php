@extends('layouts.app')
@section('title', 'Manajemen Nomor Surat')
@section('page-title', 'Manajemen Nomor Surat')

@section('header-actions')
    @if(auth()->user()->isAdmin())
    <a href="{{ route('administrasi.nomor-surat.format-settings') }}" class="btn btn-warning">
        <i class="ph ph-faders"></i> Pengaturan Format Penomoran
    </a>
    @endif
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Information Card -->
    <div class="lg:col-span-1 space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2 mb-4">
                <i class="ph ph-info text-primary"></i> Info Reset Sequence
            </h3>
            <p class="text-sm text-gray-600 mb-4 leading-relaxed">
                Reset Nomor Surat Tahunan digunakan untuk mengembalikan *sequence* (urutan) nomor surat menjadi 1 di awal tahun yang baru.
            </p>
            <div class="bg-amber-50 border border-amber-200 rounded-md p-3 text-sm text-amber-800">
                <strong>Ketentuan 2-Step Verification:</strong><br>
                Admin mengajukan reset <i class="ph ph-arrow-right"></i> Manager (Kepala Unit) menyetujui.
            </div>

            @if(auth()->user()->isAdmin())
                <hr class="divider">
                <form method="POST" action="{{ route('administrasi.nomor-surat.reset.request') }}">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Catatan Pengajuan <span class="req">*</span></label>
                        <textarea name="notes" rows="2" class="form-control" placeholder="Tulis catatan (misal: Reset untuk Tahun 2027)" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-full justify-center">
                        <i class="ph ph-paper-plane-tilt"></i> Ajukan Reset Nomor
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Requests List -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 bg-white">
                <h3 class="text-lg font-bold text-gray-800">Riwayat Pengajuan Reset Nomor</h3>
            </div>
            
            <div class="p-6">
                @if($resetRequests->isEmpty())
                    <div class="text-center py-8">
                        <i class="ph ph-clock text-4xl text-gray-300 mb-2"></i>
                        <p class="text-sm text-gray-500">Belum ada riwayat pengajuan reset nomor.</p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($resetRequests as $req)
                        <div class="border border-gray-200 rounded-lg p-4 flex flex-col md:flex-row gap-4 items-start md:items-center justify-between bg-gray-50/50">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-bold text-gray-800">Tahun Target: {{ $req->target_year }}</span>
                                    <span class="badge badge-{{ $req->status === 'approved' ? 'success' : ($req->status === 'rejected' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($req->status) }}
                                    </span>
                                </div>
                                <div class="text-xs text-gray-500 mb-2">
                                    Diajukan oleh <strong>{{ $req->requester->name }}</strong> pada {{ $req->created_at->format('d M Y H:i') }}
                                </div>
                                @if($req->notes)
                                    <div class="text-sm text-gray-700 bg-white p-2 rounded border border-gray-100 italic">"{{ $req->notes }}"</div>
                                @endif
                                
                                @if($req->status !== 'pending' && $req->approver)
                                    <div class="text-xs text-gray-500 mt-2">
                                        Di{{ $req->status == 'approved' ? 'setujui' : 'tolak' }} oleh <strong>{{ $req->approver->name }}</strong>
                                    </div>
                                @endif
                            </div>

                            @if($req->status === 'pending' && auth()->user()->isManager())
                            <div class="flex items-center gap-2 shrink-0">
                                <form method="POST" action="{{ route('administrasi.nomor-surat.reset.approve', $req->id) }}">
                                    @csrf
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Tolak permintaan reset ini?')">Tolak</button>
                                </form>
                                <form method="POST" action="{{ route('administrasi.nomor-surat.reset.approve', $req->id) }}">
                                    @csrf
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Setujui permintaan reset untuk tahun baru?')">Setujui</button>
                                </form>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection
