@extends('layouts.app')
@section('title', 'Detail Surat — ' . $mail->reference_number)
@section('page-title', 'Detail Surat')

@section('header-actions')
    @if(auth()->user()->isAdmin() || auth()->user()->isManager())
        <a href="{{ route('administrasi.nomor-surat.revisi-form', $mail->id) }}" class="btn btn-warning btn-sm"><i class="ph ph-arrows-clockwise"></i> Revisi Nomor</a>
    @endif
    <a href="{{ route('mails.edit', $mail) }}" class="btn btn-secondary btn-sm"><i class="ph ph-pencil"></i> Edit Konten</a>
    <form method="POST" action="{{ route('mails.destroy', $mail) }}" onsubmit="return confirm('Hapus surat ini?')" style="display:inline">
        @csrf @method('DELETE')
        <button type="submit" class="btn btn-danger btn-sm"><i class="ph ph-trash"></i> Hapus</button>
    </form>
@endsection

@section('content')
<div class="breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <i class="ph ph-caret-right" style="font-size:12px"></i>
    @if($mail->type==='incoming')
        <a href="{{ route('mails.incoming.index') }}">Surat Masuk</a>
    @elseif($mail->type==='outgoing')
        <a href="{{ route('mails.outgoing.index') }}">Surat Keluar</a>
    @else
        <a href="{{ route('mails.internal.index') }}">Surat Internal</a>
    @endif
    <i class="ph ph-caret-right" style="font-size:12px"></i>
    <span>{{ $mail->reference_number }}</span>
</div>

<div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start">
    <!-- Main content -->
    <div>
        <div class="card" style="margin-bottom:16px">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px;gap:12px">
                <div style="flex:1">
                    <h2 style="font-size:18px;font-weight:700;line-height:1.4">{{ $mail->subject }}</h2>
                    <div style="display:flex;gap:8px;margin-top:8px;flex-wrap:wrap">
                        <span class="badge badge-{{ ['incoming'=>'info','outgoing'=>'success','internal'=>'accent'][$mail->type] ?? 'gray' }}">
                            {{ $mail->type_label }}
                        </span>
                        <span class="badge badge-{{ $mail->status_color }}">{{ $mail->status_label }}</span>
                        <span class="badge badge-{{ $mail->priority_color }}">{{ $mail->priority_label }}</span>
                        @if($mail->classification !== 'open')
                            <span class="badge badge-warning">{{ ucfirst($mail->classification) }}</span>
                        @endif
                    </div>
                </div>
                <!-- Status update -->
                <form method="POST" action="{{ route('mails.status', $mail) }}" style="display:flex;gap:8px;align-items:center">
                    @csrf @method('PATCH')
                    <select name="status" class="form-control" style="max-width:150px;padding:6px 10px;font-size:12px" onchange="this.form.submit()">
                        @foreach(['draft'=>'Draft','pending'=>'Pending','in_progress'=>'Diproses','completed'=>'Selesai','archived'=>'Diarsipkan'] as $val => $lbl)
                            <option value="{{ $val }}" {{ $mail->status === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </form>
            </div>

            <hr class="divider">

            <div style="font-size:14px;line-height:1.7;color:var(--text-secondary);white-space:pre-wrap">{{ $mail->body }}</div>

            @if($mail->notes)
                <div style="margin-top:16px;padding:12px;background:var(--bg-hover);border-radius:8px;border-left:3px solid var(--accent)">
                    <div style="font-size:12px;font-weight:600;color:var(--text-muted);margin-bottom:6px">CATATAN INTERNAL</div>
                    <div style="font-size:13px;color:var(--text-secondary)">{{ $mail->notes }}</div>
                </div>
            @endif

            @if(auth()->user()->isManager() && $mail->referenceLogs->count() > 0)
                <div style="margin-top:16px;padding:12px;background:var(--bg-hover);border-radius:8px;border-left:3px solid var(--warning)">
                    <div style="font-size:12px;font-weight:600;color:var(--text-muted);margin-bottom:6px">RIWAYAT PERUBAHAN NOMOR REFERENSI</div>
                    <ul style="font-size:12px;color:var(--text-secondary);padding-left:16px;margin:0">
                        @foreach($mail->referenceLogs as $log)
                            <li style="margin-bottom:4px;">Diubah dari <b>{{ $log->old_reference }}</b> ke <b>{{ $log->new_reference }}</b> oleh <b>{{ optional($log->user)->name }}</b> pada {{ $log->created_at->format('d M Y H:i') }}<br><i>Alasan: {{ $log->reason }}</i></li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($mail->attachment_path)
                <div style="margin-top:16px">
                    <a href="{{ asset('storage/' . $mail->attachment_path) }}" target="_blank" class="btn btn-secondary btn-sm">
                        <i class="ph ph-paperclip"></i> {{ $mail->attachment_name ?? 'Lihat Lampiran' }}
                    </a>
                </div>
            @endif
        </div>

        <!-- Dispositions -->
        <div class="card">
            <div class="card-title" style="margin-bottom:16px">
                <i class="ph ph-git-branch"></i> Riwayat Disposisi
                <span style="margin-left:auto;font-size:12px;font-weight:400;color:var(--text-muted)">{{ $mail->dispositions->count() }} disposisi</span>
            </div>

            @forelse($mail->dispositions as $disp)
            <div style="padding:14px;border:1px solid var(--border);border-radius:8px;margin-bottom:10px">
                <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap">
                    <div>
                        <div style="font-size:13px;font-weight:600">
                            {{ $disp->fromUser->name }}
                            <i class="ph ph-arrow-right" style="font-size:12px;margin:0 4px;color:var(--text-muted)"></i>
                            {{ $disp->toUser->name }}
                        </div>
                        <div style="font-size:12px;color:var(--text-muted);margin-top:3px">
                            {{ $disp->action_label }}
                            @if($disp->due_date)
                                &bull; Batas: <strong>{{ $disp->due_date->format('d M Y') }}</strong>
                                @if($disp->isOverdue()) <span class="badge badge-danger" style="margin-left:4px">Terlambat</span> @endif
                            @endif
                        </div>
                    </div>
                    <div style="display:flex;gap:8px;align-items:center">
                        <span class="badge badge-{{ $disp->status_color }}">{{ $disp->status_label }}</span>
                        <a href="{{ route('dispositions.show', $disp) }}" class="btn btn-secondary btn-sm">Detail</a>
                    </div>
                </div>
                <div style="margin-top:10px;padding:10px;background:var(--bg-hover);border-radius:6px;font-size:13px;color:var(--text-secondary)">
                    {{ $disp->instruction }}
                </div>
                @if($disp->response_notes)
                    <div style="margin-top:8px;padding:10px;background:rgba(34,197,94,0.05);border:1px solid rgba(34,197,94,0.2);border-radius:6px;font-size:13px;color:var(--success)">
                        <strong>Respon:</strong> {{ $disp->response_notes }}
                    </div>
                @endif
            </div>
            @empty
                <p class="text-muted text-sm" style="text-align:center;padding:20px 0">Belum ada disposisi</p>
            @endforelse

            <!-- Add Disposition Form (managers only) -->
            @if(auth()->user()->isManager())
            <hr class="divider" style="margin:16px 0">
            <div style="font-size:14px;font-weight:600;margin-bottom:12px">Buat Disposisi Baru</div>
            <form method="POST" action="{{ route('dispositions.store') }}">
                @csrf
                <input type="hidden" name="mail_id" value="{{ $mail->id }}">
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label" for="to_user_id">Ditujukan kepada <span class="req">*</span></label>
                        <select id="to_user_id" name="to_user_id" class="form-control" required>
                            <option value="">— Pilih Penerima —</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->role_label }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="action_type">Tipe Tindakan <span class="req">*</span></label>
                        <select id="action_type" name="action_type" class="form-control" required>
                            <option value="for_action">Untuk Ditindaklanjuti</option>
                            <option value="for_review">Untuk Ditelaah</option>
                            <option value="for_information">Untuk Diketahui</option>
                            <option value="for_approval">Untuk Disetujui</option>
                            <option value="for_filing">Untuk Diarsipkan</option>
                            <option value="for_reply">Untuk Dibalas</option>
                            <option value="coordinate">Koordinasikan</option>
                            <option value="other">Lainnya</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="due_date">Batas Waktu</label>
                        <input type="date" id="due_date" name="due_date" class="form-control" min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="instruction">Instruksi / Perintah <span class="req">*</span></label>
                    <textarea id="instruction" name="instruction" class="form-control" rows="3" placeholder="Tuliskan instruksi untuk penerima disposisi..." required></textarea>
                </div>
                <button type="submit" class="btn btn-primary"><i class="ph ph-paper-plane-tilt"></i> Kirim Disposisi</button>
            </form>
            @endif
        </div>
    </div>

    <!-- Sidebar info -->
    <div>
        <div class="card" style="margin-bottom:16px">
            <div class="card-title"><i class="ph ph-info"></i> Informasi Surat</div>
            <dl style="display:grid;gap:12px">
                <div>
                    <dt style="font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em">Nomor Referensi</dt>
                    <dd style="font-size:14px;margin-top:3px;font-family:monospace;color:var(--accent)">{{ $mail->reference_number }}</dd>
                </div>
                <div>
                    <dt style="font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em">Tanggal Surat</dt>
                    <dd style="font-size:14px;margin-top:3px">{{ $mail->tanggal_surat->format('d F Y') }}</dd>
                </div>
                @if($mail->received_date)
                <div>
                    <dt style="font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em">Tanggal Terima</dt>
                    <dd style="font-size:14px;margin-top:3px">{{ $mail->received_date->format('d F Y') }}</dd>
                </div>
                @endif
                <div>
                    <dt style="font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em">Pengirim</dt>
                    <dd style="font-size:14px;margin-top:3px">{{ $mail->sender_name }}
                        @if($mail->sender_organization)<div class="text-xs text-muted">{{ $mail->sender_organization }}</div>@endif
                    </dd>
                </div>
                <div>
                    <dt style="font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em">Penerima</dt>
                    <dd style="font-size:14px;margin-top:3px">{{ $mail->recipient_name }}
                        @if($mail->recipient_department)<div class="text-xs text-muted">{{ $mail->recipient_department }}</div>@endif
                    </dd>
                </div>
                @if($mail->assignee)
                <div>
                    <dt style="font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em">Ditugaskan ke</dt>
                    <dd style="font-size:14px;margin-top:3px">{{ $mail->assignee->name }}</dd>
                </div>
                @endif
                <div>
                    <dt style="font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em">Dibuat oleh</dt>
                    <dd style="font-size:14px;margin-top:3px">{{ $mail->creator->name }}
                        <div class="text-xs text-muted">{{ $mail->created_at->format('d M Y, H:i') }}</div>
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</div>
@endsection
