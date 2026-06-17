@extends('layouts.app')
@section('title', 'Detail Disposisi')
@section('page-title', 'Detail Disposisi')

@section('content')
<div class="breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <i class="ph ph-caret-right" style="font-size:12px"></i>
    <a href="{{ route('dispositions.index') }}">Disposisi</a>
    <i class="ph ph-caret-right" style="font-size:12px"></i>
    <span>#{{ $disposition->id }}</span>
</div>

<div style="display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start">
    <div>
        <!-- Mail Summary -->
        <div class="card" style="margin-bottom:16px">
            <div class="card-title"><i class="ph ph-envelope-simple"></i> Surat Terkait</div>
            <div style="padding:14px;background:var(--bg-hover);border-radius:8px">
                <div style="font-size:12px;font-weight:600;color:var(--accent);margin-bottom:6px;font-family:monospace">
                    {{ $disposition->mail->reference_number }}
                </div>
                <div style="font-size:15px;font-weight:600;margin-bottom:8px">{{ $disposition->mail->subject }}</div>
                <div style="display:flex;gap:8px;flex-wrap:wrap">
                    <span class="badge badge-{{ ['incoming'=>'info','outgoing'=>'success','internal'=>'accent'][$disposition->mail->type] ?? 'gray' }}">
                        {{ $disposition->mail->type_label }}
                    </span>
                    <span class="badge badge-{{ $disposition->mail->status_color }}">{{ $disposition->mail->status_label }}</span>
                    <span class="badge badge-{{ $disposition->mail->priority_color }}">{{ $disposition->mail->priority_label }}</span>
                </div>
            </div>
            <div style="margin-top:12px">
                <a href="{{ route('mails.show', $disposition->mail) }}" class="btn btn-secondary btn-sm">
                    <i class="ph ph-arrow-square-out"></i> Lihat Surat Lengkap
                </a>
            </div>
        </div>

        <!-- Disposition Detail -->
        <div class="card" style="margin-bottom:16px">
            <div class="card-title"><i class="ph ph-git-branch"></i> Detail Disposisi</div>

            <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;padding:12px;background:var(--bg-hover);border-radius:8px">
                <div style="text-align:center">
                    <img src="{{ $disposition->fromUser->avatar_url }}" style="width:40px;height:40px;border-radius:50%" onerror="this.style.display='none'">
                    <div style="font-size:12px;font-weight:600;margin-top:4px">{{ $disposition->fromUser->name }}</div>
                    <div style="font-size:11px;color:var(--text-muted)">{{ $disposition->fromUser->role_label }}</div>
                </div>
                <div style="flex:1;text-align:center;color:var(--text-muted)">
                    <i class="ph ph-arrow-right" style="font-size:24px"></i>
                    <div style="font-size:12px;margin-top:4px">{{ $disposition->action_label }}</div>
                </div>
                <div style="text-align:center">
                    <img src="{{ $disposition->toUser->avatar_url }}" style="width:40px;height:40px;border-radius:50%" onerror="this.style.display='none'">
                    <div style="font-size:12px;font-weight:600;margin-top:4px">{{ $disposition->toUser->name }}</div>
                    <div style="font-size:11px;color:var(--text-muted)">{{ $disposition->toUser->role_label }}</div>
                </div>
            </div>

            <div style="margin-bottom:16px">
                <div style="font-size:12px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px">Instruksi</div>
                <div style="padding:14px;background:var(--bg-hover);border-radius:8px;font-size:14px;line-height:1.6;color:var(--text-secondary);white-space:pre-wrap">{{ $disposition->instruction }}</div>
            </div>

            @if($disposition->response_notes)
            <div>
                <div style="font-size:12px;font-weight:600;color:var(--success);text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px">Respon / Tindak Lanjut</div>
                <div style="padding:14px;background:rgba(34,197,94,0.05);border:1px solid rgba(34,197,94,0.2);border-radius:8px;font-size:14px;line-height:1.6;color:var(--text-secondary);white-space:pre-wrap">{{ $disposition->response_notes }}</div>
                
                @if($disposition->response_attachment_path)
                    <div style="margin-top:10px; display:flex; align-items:center; gap:8px; padding:12px; background:#f4fbf7; border:1px dashed rgba(34,197,94,0.3); border-radius:8px;">
                        <i class="ph ph-paperclip text-success" style="font-size:20px;"></i>
                        <div style="flex:1; min-width:0;">
                            <div style="font-size:11px; font-weight:600; color:var(--text-muted); text-transform:uppercase;">Lampiran Tanggapan</div>
                            <a href="{{ asset('storage/' . $disposition->response_attachment_path) }}" target="_blank" style="font-size:13px; font-weight:700; color:#1e5c45; text-decoration:underline; word-break:break-all;" class="truncate">
                                {{ $disposition->response_attachment_name ?? 'Download Lampiran' }}
                            </a>
                        </div>
                    </div>
                @endif

                @if($disposition->responded_at)
                    <div style="font-size:12px;color:var(--text-muted);margin-top:6px">
                        Direspon pada: {{ $disposition->responded_at->format('d M Y, H:i') }}
                    </div>
                @endif
            </div>
            @endif
        </div>

        <!-- Response Form -->
        @if($disposition->to_user_id === auth()->id() && in_array($disposition->status, ['pending','in_progress']))
        <div class="card">
            <div class="card-title"><i class="ph ph-chat-dots"></i> Berikan Respon</div>
            <form method="POST" action="{{ route('dispositions.respond', $disposition) }}" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="response_notes">Catatan Tindak Lanjut <span class="req">*</span></label>
                    <textarea id="response_notes" name="response_notes" class="form-control" rows="4" placeholder="Jelaskan tindakan yang sudah atau akan dilakukan..." required></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label" for="response_attachment">Lampirkan Berkas Bukti (Opsional)</label>
                    <input type="file" id="response_attachment" name="response_attachment" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                    <span style="font-size:11px; color:var(--text-muted); display:block; margin-top:4px;">Format yang didukung: PDF, Word, Excel, JPG, PNG (Maksimal 10MB)</span>
                </div>
                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label">Status Update</label>
                    <div style="display:flex;gap:10px">
                        <button type="submit" name="status" value="in_progress" class="btn btn-warning">
                            <i class="ph ph-spinner-gap"></i> Tandai Sedang Diproses
                        </button>
                        <button type="submit" name="status" value="completed" class="btn btn-success">
                            <i class="ph ph-check-circle"></i> Tandai Selesai
                        </button>
                    </div>
                </div>
            </form>
        </div>
        @endif
    </div>

    <!-- Info panel -->
    <div class="card">
        <div class="card-title"><i class="ph ph-info"></i> Informasi</div>
        <dl style="display:grid;gap:14px">
            <div>
                <dt style="font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em">Status</dt>
                <dd style="margin-top:5px"><span class="badge badge-{{ $disposition->status_color }}">{{ $disposition->status_label }}</span></dd>
            </div>
            <div>
                <dt style="font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em">Tipe Tindakan</dt>
                <dd style="font-size:14px;margin-top:4px">{{ $disposition->action_label }}</dd>
            </div>
            @if($disposition->due_date)
            <div>
                <dt style="font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em">Batas Waktu</dt>
                <dd style="font-size:14px;margin-top:4px;color:{{ $disposition->isOverdue() ? 'var(--danger)' : 'inherit' }}">
                    {{ $disposition->due_date->format('d M Y') }}
                    @if($disposition->isOverdue()) <span class="badge badge-danger" style="margin-left:6px">Terlambat</span> @endif
                </dd>
            </div>
            @endif
            <div>
                <dt style="font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em">Dibuat</dt>
                <dd style="font-size:14px;margin-top:4px">{{ $disposition->created_at->format('d M Y, H:i') }}</dd>
            </div>
            @if($disposition->read_at)
            <div>
                <dt style="font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em">Dibaca</dt>
                <dd style="font-size:14px;margin-top:4px">{{ $disposition->read_at->format('d M Y, H:i') }}</dd>
            </div>
            @endif
        </dl>
    </div>
</div>
@endsection
