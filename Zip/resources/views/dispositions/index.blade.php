@extends('layouts.app')
@section('title', 'Disposisi')
@section('page-title', 'Manajemen Disposisi')

@section('content')
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
    <!-- Received Dispositions -->
    <div class="card">
        <div class="card-title"><i class="ph ph-inbox"></i> Disposisi Diterima</div>

        @if($received->isEmpty())
            <p class="text-muted text-sm" style="text-align:center;padding:30px 0">Tidak ada disposisi masuk</p>
        @else
            @foreach($received as $disp)
            <a href="{{ route('dispositions.show', $disp) }}" style="text-decoration:none;color:inherit;display:block">
                <div style="padding:12px;border:1px solid {{ !$disp->read_at ? 'var(--accent)' : 'var(--border)' }};border-radius:8px;margin-bottom:8px;background:{{ !$disp->read_at ? 'var(--accent-light)' : 'transparent' }};transition:border-color 0.15s"
                     onmouseover="this.style.borderColor='var(--border-light)'" onmouseout="this.style.borderColor='{{ !$disp->read_at ? 'var(--accent)' : 'var(--border)' }}'">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px">
                        <div style="flex:1">
                            <div style="font-size:13px;font-weight:600">
                                @if(!$disp->read_at)<span class="badge badge-accent" style="margin-right:6px;font-size:10px">BARU</span>@endif
                                {{ Str::limit($disp->mail->subject, 55) }}
                            </div>
                            <div style="font-size:12px;color:var(--text-muted);margin-top:4px">
                                Dari: <strong>{{ $disp->fromUser->name }}</strong>
                                &bull; {{ $disp->action_label }}
                            </div>
                            @if($disp->due_date)
                                <div style="font-size:12px;margin-top:3px;color:{{ $disp->isOverdue() ? 'var(--danger)' : 'var(--text-muted)' }}">
                                    <i class="ph ph-calendar"></i>
                                    Batas: {{ $disp->due_date->format('d M Y') }}
                                    @if($disp->isOverdue()) <span class="badge badge-danger" style="margin-left:4px">Terlambat</span> @endif
                                </div>
                            @endif
                        </div>
                        <span class="badge badge-{{ $disp->status_color }}">{{ $disp->status_label }}</span>
                    </div>
                </div>
            </a>
            @endforeach

            @if($received->hasPages())
                <div class="pagination-wrapper" style="margin-top:12px">
                    <span class="text-xs text-muted">{{ $received->total() }} total</span>
                    <div class="pagination">
                        @if(!$received->onFirstPage())
                            <a href="{{ $received->previousPageUrl() }}" class="page-btn">‹</a>
                        @endif
                        @foreach($received->getUrlRange(1, $received->lastPage()) as $page => $url)
                            <a href="{{ $url }}" class="page-btn {{ $page == $received->currentPage() ? 'active' : '' }}">{{ $page }}</a>
                        @endforeach
                        @if($received->hasMorePages())
                            <a href="{{ $received->nextPageUrl() }}" class="page-btn">›</a>
                        @endif
                    </div>
                </div>
            @endif
        @endif
    </div>

    <!-- Sent Dispositions -->
    <div class="card">
        <div class="card-title"><i class="ph ph-paper-plane-tilt"></i> Disposisi Dikirim</div>

        @if($sent->isEmpty())
            <p class="text-muted text-sm" style="text-align:center;padding:30px 0">Belum pernah membuat disposisi</p>
        @else
            @foreach($sent as $disp)
            <a href="{{ route('dispositions.show', $disp) }}" style="text-decoration:none;color:inherit;display:block">
                <div style="padding:12px;border:1px solid var(--border);border-radius:8px;margin-bottom:8px;transition:border-color 0.15s"
                     onmouseover="this.style.borderColor='var(--border-light)'" onmouseout="this.style.borderColor='var(--border)'">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px">
                        <div style="flex:1">
                            <div style="font-size:13px;font-weight:600">{{ Str::limit($disp->mail->subject, 55) }}</div>
                            <div style="font-size:12px;color:var(--text-muted);margin-top:4px">
                                Kepada: <strong>{{ $disp->toUser->name }}</strong>
                                &bull; {{ $disp->action_label }}
                            </div>
                            <div style="font-size:12px;color:var(--text-muted);margin-top:2px">
                                {{ $disp->created_at->format('d M Y, H:i') }}
                            </div>
                        </div>
                        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:6px">
                            <span class="badge badge-{{ $disp->status_color }}">{{ $disp->status_label }}</span>
                            @if($disp->status === 'pending')
                                <form method="POST" action="{{ route('dispositions.destroy', $disp) }}" onsubmit="return confirm('Batalkan disposisi ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" style="font-size:11px">Batalkan</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </a>
            @endforeach
        @endif
    </div>
</div>
@endsection
