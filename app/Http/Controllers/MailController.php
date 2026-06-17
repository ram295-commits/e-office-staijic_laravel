<?php

namespace App\Http\Controllers;

use App\Models\Disposition;
use App\Models\Mail;
use App\Models\MailReferenceLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MailController extends Controller
{
    /** Roman numeral month map shared by store/update. */
    private const ROMAN_MONTHS = [
        '01' => 'I',  '02' => 'II',  '03' => 'III', '04' => 'IV',
        '05' => 'V',  '06' => 'VI',  '07' => 'VII', '08' => 'VIII',
        '09' => 'IX', '10' => 'X',   '11' => 'XI',  '12' => 'XII',
    ];

    public function dashboard()
    {
        $this->authorize('viewAny', Mail::class);

        $user = Auth::user();

        // 1. Calculate statistics based on role access
        if ($user->isManager() || $user->isAdmin()) {
            $stats = [
                'incoming_total'  => Mail::incoming()->count('*'),
                'outgoing_total'  => Mail::outgoing()->count('*'),
                'internal_total'  => Mail::internal()->count('*'),
                'pending_count'   => Mail::pending()->count('*'),
                'my_dispositions' => Disposition::where('to_user_id', $user->id)->where('status', 'pending')->count('*'),
            ];
        } else {
            $userUnitIds = $user->units()->pluck('units.id');
            $staffMailsBase = Mail::where(function ($sub) use ($user, $userUnitIds) {
                $sub->where('created_by', $user->id)
                    ->orWhere('assigned_to', $user->id)
                    ->orWhereIn('unit_id', $userUnitIds)
                    ->orWhereHas('dispositions', function ($disp) use ($user) {
                        $disp->where('to_user_id', $user->id);
                    });
            });

            $stats = [
                'incoming_total'  => (clone $staffMailsBase)->incoming()->count('*'),
                'outgoing_total'  => (clone $staffMailsBase)->outgoing()->count('*'),
                'internal_total'  => (clone $staffMailsBase)->internal()->count('*'),
                'pending_count'   => (clone $staffMailsBase)->pending()->count('*'),
                'my_dispositions' => Disposition::where('to_user_id', $user->id)->where('status', 'pending')->count('*'),
            ];
        }

        // 2. Fetch and sanitize recent mails
        $recentMailsRaw = Mail::with(['creator', 'assignee', 'unit', 'documentType', 'dispositions.fromUser', 'dispositions.toUser'])
            ->when(!$user->isManager() && !$user->isAdmin(), function ($q) use ($user) {
                $userUnitIds = $user->units()->pluck('units.id');
                $q->where(function ($sub) use ($user, $userUnitIds) {
                    $sub->where('created_by', $user->id)
                        ->orWhere('assigned_to', $user->id)
                        ->orWhereIn('unit_id', $userUnitIds)
                        ->orWhereHas('dispositions', function ($disp) use ($user) {
                            $disp->where('to_user_id', $user->id);
                        });
                });
            })
            ->latest()
            ->limit(50)
            ->get();

        $recentMails = $recentMailsRaw->map(function ($mail) use ($user) {
            return [
                'id' => $mail->id,
                'reference_number' => $mail->reference_number,
                'type' => $mail->type,
                'type_label' => $mail->type_label,
                'subject' => $mail->subject,
                'body' => $mail->body,
                'sender_name' => $mail->sender_name,
                'sender_organization' => $mail->sender_organization,
                'sender_email' => $mail->sender_email,
                'recipient_name' => $mail->recipient_name,
                'recipient_department' => $mail->recipient_department,
                'recipient_email' => $mail->recipient_email,
                'tanggal_surat' => $mail->tanggal_surat ? $mail->tanggal_surat->format('Y-m-d') : null,
                'tanggal_surat_formatted' => $mail->tanggal_surat ? $mail->tanggal_surat->format('d M Y') : null,
                'received_date' => $mail->received_date ? $mail->received_date->format('Y-m-d') : null,
                'received_date_formatted' => $mail->received_date ? $mail->received_date->format('d M Y') : null,
                'priority' => $mail->priority,
                'priority_label' => $mail->priority_label,
                'priority_color' => $mail->priority_color,
                'classification' => $mail->classification,
                'status' => $mail->status,
                'status_label' => $mail->status_label,
                'status_color' => $mail->status_color,
                'attachment_path' => $mail->attachment_path ? asset('storage/' . $mail->attachment_path) : null,
                'attachment_name' => $mail->attachment_name,
                'creator_name' => $mail->creator ? $mail->creator->name : null,
                'assignee_name' => $mail->assignee ? $mail->assignee->name : null,
                'unit_name' => $mail->unit ? $mail->unit->name : null,
                'document_type_name' => $mail->documentType ? $mail->documentType->name : null,
                'can_update_status' => $user->can('updateStatus', $mail),
                'can_archive' => $user->isManager() || $user->isAdmin(),
                'has_my_pending_disposition' => $mail->dispositions->where('to_user_id', $user->id)->where('status', 'pending')->isNotEmpty(),
                'dispositions' => $mail->dispositions->map(function ($disp) {
                    return [
                        'id' => $disp->id,
                        'from_user_name' => $disp->fromUser ? $disp->fromUser->name : null,
                        'to_user_name' => $disp->toUser ? $disp->toUser->name : null,
                        'instruction' => $disp->instruction,
                        'action_label' => $disp->action_label,
                        'due_date_formatted' => $disp->due_date ? $disp->due_date->format('d M Y') : null,
                        'status_label' => $disp->status_label,
                        'status_color' => $disp->status_color,
                        'response_notes' => $disp->response_notes,
                        'responded_at_formatted' => $disp->responded_at ? $disp->responded_at->format('d M Y H:i') : null,
                        'response_attachment_path' => $disp->response_attachment_path ? asset('storage/' . $disp->response_attachment_path) : null,
                        'response_attachment_name' => $disp->response_attachment_name,
                    ];
                })->values()->toArray(),
            ];
        });

        // 3. Get active users for disposition widget
        $users = User::active()->where('id', '!=', $user->id)->orderBy('name')->get(['id', 'name', 'position', 'department']);

        // 4. Compile monthly statistics for last 6 months (DB-agnostic)
        $monthlyStats = [];
        $indonesianMonths = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
            7 => 'Jul', 8 => 'Ags', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
        ];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthKey = $date->format('Y-m');
            $monthLabel = $indonesianMonths[$date->month] . ' ' . $date->year;
            $monthlyStats[$monthKey] = [
                'label'    => $monthLabel,
                'incoming' => 0,
                'outgoing' => 0,
                'internal' => 0,
            ];
        }

        $startDate = now()->subMonths(5)->startOfMonth();
        $mailsForStats = Mail::where('tanggal_surat', '>=', $startDate)
            ->when(!$user->isManager() && !$user->isAdmin(), function ($q) use ($user) {
                $userUnitIds = $user->units()->pluck('units.id');
                $q->where(function ($sub) use ($user, $userUnitIds) {
                    $sub->where('created_by', $user->id)
                        ->orWhere('assigned_to', $user->id)
                        ->orWhereIn('unit_id', $userUnitIds)
                        ->orWhereHas('dispositions', function ($disp) use ($user) {
                            $disp->where('to_user_id', $user->id);
                        });
                });
            })
            ->get(['type', 'tanggal_surat']);

        foreach ($mailsForStats as $mail) {
            if ($mail->tanggal_surat) {
                $mKey = $mail->tanggal_surat->format('Y-m');
                if (isset($monthlyStats[$mKey]) && in_array($mail->type, ['incoming', 'outgoing', 'internal'])) {
                    $monthlyStats[$mKey][$mail->type]++;
                }
            }
        }

        // 5. Get current pending dispositions
        $myDispositions = Disposition::with(['mail', 'fromUser'])
            ->where('to_user_id', $user->id)
            ->where('status', 'pending')
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard', compact('stats', 'recentMails', 'myDispositions', 'users', 'monthlyStats'));
    }

    // ─── INCOMING ────────────────────────────────────────────────────────────

    public function indexIncoming(Request $request)
    {
        $this->authorize('viewAny', Mail::class);

        $query = Mail::incoming()->with('creator', 'assignee');
        return $this->applyFilters($query, $request, 'mails.incoming');
    }

    public function createIncoming()
    {
        $this->authorize('create', Mail::class);

        return view('mails.create', ['type' => 'incoming', 'title' => 'Surat Masuk']);
    }

    // ─── OUTGOING ────────────────────────────────────────────────────────────

    public function indexOutgoing(Request $request)
    {
        $this->authorize('viewAny', Mail::class);

        $query = Mail::outgoing()->with('creator', 'assignee');
        return $this->applyFilters($query, $request, 'mails.outgoing');
    }

    public function createOutgoing()
    {
        $this->authorize('create', Mail::class);

        return view('mails.create', ['type' => 'outgoing', 'title' => 'Surat Keluar']);
    }

    // ─── INTERNAL ────────────────────────────────────────────────────────────

    public function indexInternal(Request $request)
    {
        $this->authorize('viewAny', Mail::class);

        $query = Mail::internal()->with('creator', 'assignee');
        return $this->applyFilters($query, $request, 'mails.internal');
    }

    public function createInternal()
    {
        $this->authorize('create', Mail::class);

        return view('mails.create', ['type' => 'internal', 'title' => 'Surat Internal']);
    }

    // ─── SHARED CRUD ─────────────────────────────────────────────────────────

    private function applyFilters(Builder $query, Request $request, string $view)
    {
        if ($s = $request->search) {
            $query->where(function (Builder $q) use ($s) {
                $q->where('subject', 'like', "%{$s}%")
                  ->orWhere('reference_number', 'like', "%{$s}%")
                  ->orWhere('sender_name', 'like', "%{$s}%")
                  ->orWhere('recipient_name', 'like', "%{$s}%");
            });
        }
        if ($status = $request->status) {
            $query->where('status', $status);
        }
        if ($priority = $request->priority) {
            $query->where('priority', $priority);
        }
        if ($from = $request->from_date) {
            $query->where('tanggal_surat', '>=', $from);
        }
        if ($to = $request->to_date) {
            $query->where('tanggal_surat', '<=', $to);
        }

        $mails = $query->latest('tanggal_surat')->paginate(15)->withQueryString();
        return view($view, compact('mails'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Mail::class);

        $validated = $request->validate([
            'document_type_id'      => 'required|exists:document_types,id',
            'type'                  => 'required|in:incoming,outgoing,internal',
            'subject'               => 'required|string|max:255',
            'body'                  => 'required|string',
            'sender_name'           => 'required|string|max:150',
            'sender_organization'   => 'nullable|string|max:150',
            'sender_email'          => 'nullable|email|max:150',
            'recipient_name'        => 'required|string|max:150',
            'recipient_department'  => 'nullable|string|max:150',
            'recipient_email'       => 'nullable|email|max:150',
            'tanggal_surat'         => 'required|date',
            'received_date'         => 'nullable|date',
            'priority'              => 'required|in:normal,urgent,very_urgent',
            'classification'        => 'required|in:open,confidential,secret',
            'assigned_to'           => 'nullable|exists:users,id',
            'notes'                 => 'nullable|string',
            'attachment'            => 'nullable|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png',
            'sequence_number'       => 'nullable|integer|min:1',
            'sender_unit'           => 'nullable|string|max:150',
            'jenjang'               => 'nullable|string|max:150',
        ]);

        if (!Auth::user()->canUseDocumentType($validated['document_type_id'])) {
            abort(403, 'Anda tidak berhak membuat dokumen dengan jenis ini.');
        }

        // Handle file upload before transaction (I/O outside DB lock)
        $attachmentPath = null;
        $attachmentName = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentPath = $file->store('mail-attachments', 'public');
            $attachmentName = $file->getClientOriginalName();
        }

        // Validation response placeholder for errors raised inside the transaction
        $validationError = null;
        $mail = null;

        DB::transaction(function () use (&$validated, &$mail, &$validationError, $attachmentPath, $attachmentName) {
            $docType = \App\Models\DocumentType::with('unit')->findOrFail($validated['document_type_id']);
            $unitId = $docType->unit_id;
            $validated['unit_id'] = $unitId;

            $tanggalSurat = $validated['tanggal_surat'];
            $year = \Carbon\Carbon::parse($tanggalSurat)->year;

            // Auto-assign sequence_number — lockForUpdate prevents concurrent duplicates
            if (empty($validated['sequence_number'])) {
                $maxSeq = Mail::where('unit_id', $unitId)
                    ->whereYear('tanggal_surat', $year)
                    ->lockForUpdate()
                    ->max('sequence_number') ?? 0;
                $validated['sequence_number'] = $maxSeq + 1;
            } else {
                // Check composite collision: (Tahun, KodeSurat, Urut Kategori)
                $collision = Mail::whereYear('tanggal_surat', $year)
                    ->where('document_type_id', $docType->id)
                    ->where('sequence_number', $validated['sequence_number'])
                    ->lockForUpdate()
                    ->exists();
                if ($collision) {
                    $validationError = ['sequence_number' => 'Nomor urut kategori tersebut sudah digunakan untuk jenis dokumen ini pada tahun yang sama.'];
                    return;
                }
            }

            // Set is_backdated automatically
            $latestMail = Mail::where('unit_id', $unitId)
                ->latest('tanggal_surat')
                ->first();
            $isBackdated = $latestMail && \Carbon\Carbon::parse($tanggalSurat)->lt($latestMail->tanggal_surat);
            $validated['is_backdated'] = $isBackdated;

            // Verify timeline sequence validation if backdated
            if ($isBackdated) {
                if (!$this->validateBackdateSequence($unitId, $tanggalSurat, $validated['sequence_number'])) {
                    $validationError = ['sequence_number' => 'Nomor urut kategori tidak cocok dengan urutan kronologis tanggal atau tidak mengisi slot kosong yang valid.'];
                    return;
                }
            }

            // Generate reference number using LetterFormat
            $format = \App\Models\LetterFormat::where('type', $validated['type'])->first();
            $formatString = $format ? $format->format_string : '[NO_URUT]/[KODE_UNIT]/STAI-JIC/[BULAN_ROMAWI]/[TAHUN]';
            $monthStr = \Carbon\Carbon::parse($tanggalSurat)->format('m');
            $romanMonth = self::ROMAN_MONTHS[$monthStr] ?? $monthStr;

            $validated['reference_number'] = str_replace(
                ['[NO_URUT]', '[KODE_UNIT]', '[BULAN_ROMAWI]', '[TAHUN]'],
                [str_pad($validated['sequence_number'], 3, '0', STR_PAD_LEFT), $docType->unit->code, $romanMonth, $year],
                $formatString
            );

            $validated['created_by']      = Auth::id();
            $validated['status']          = 'pending';
            $validated['attachment_path'] = $attachmentPath;
            $validated['attachment_name'] = $attachmentName;

            $mail = Mail::create($validated);
        });

        if ($validationError) {
            return back()->withInput()->withErrors($validationError);
        }

        return redirect()->route('mails.' . $validated['type'] . '.show', $mail)
            ->with('success', 'Surat berhasil disimpan dengan nomor ' . $mail->reference_number);
    }

    public function show(Mail $mail)
    {
        $this->authorize('view', $mail);

        $mail->load('creator', 'assignee', 'dispositions.fromUser', 'dispositions.toUser');

        if (!$mail->read_at && $mail->assigned_to == Auth::id()) {
            $mail->update(['read_at' => now()]);
        }

        $users = User::active()->where('id', '!=', Auth::id())->orderBy('name')->get();
        return view('mails.show', compact('mail', 'users'));
    }

    public function edit(Mail $mail)
    {
        $this->authorize('update', $mail);

        return view('mails.edit', compact('mail'));
    }

    public function update(Request $request, Mail $mail)
    {
        $this->authorize('update', $mail);

        $validated = $request->validate([
            'document_type_id'      => 'required|exists:document_types,id',
            'subject'               => 'required|string|max:255',
            'body'                  => 'required|string',
            'sender_name'           => 'required|string|max:150',
            'sender_organization'   => 'nullable|string|max:150',
            'sender_email'          => 'nullable|email|max:150',
            'recipient_name'        => 'required|string|max:150',
            'recipient_department'  => 'nullable|string|max:150',
            'recipient_email'       => 'nullable|email|max:150',
            'tanggal_surat'         => 'required|date',
            'received_date'         => 'nullable|date',
            'priority'              => 'required|in:normal,urgent,very_urgent',
            'classification'        => 'required|in:open,confidential,secret',
            'status'                => 'required|in:draft,pending,in_progress,completed,archived',
            'assigned_to'           => 'nullable|exists:users,id',
            'notes'                 => 'nullable|string',
            'attachment'            => 'nullable|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png',
            'sequence_number'       => 'nullable|integer|min:1',
            'sender_unit'           => 'nullable|string|max:150',
            'jenjang'               => 'nullable|string|max:150',
        ]);

        if (!Auth::user()->canUseDocumentType($validated['document_type_id'])) {
            abort(403, 'Anda tidak berhak membuat dokumen dengan jenis ini.');
        }

        // Pre-flight status-transition checks (outside transaction — read-only)
        $oldStatus = $mail->status;
        $newStatus = $validated['status'];

        if ($oldStatus !== $newStatus) {
            $user = Auth::user();

            $allowed = [
                'draft'       => ['pending'],
                'pending'     => ['in_progress', 'draft'],
                'in_progress' => ['completed'],
                'completed'   => ['archived'],
                'archived'    => [],
            ];

            if (!in_array($newStatus, $allowed[$oldStatus] ?? [])) {
                return back()->withInput()->withErrors(['status' => 'Transisi status dari ' . $mail->getStatusLabelAttribute() . ' ke status baru tidak diperbolehkan.']);
            }

            if (!$user->isManager() && !$user->isAdmin()) {
                $isAssignee = ($mail->assigned_to == $user->id) || \App\Models\Disposition::where('mail_id', $mail->id)
                    ->where('to_user_id', $user->id)
                    ->where('status', 'in_progress')
                    ->exists();
                $isCreator = $mail->created_by == $user->id;

                if (!$isCreator && !$isAssignee) {
                    abort(403, 'Anda tidak memiliki hak untuk mengubah status dokumen ini.');
                }

                if (!in_array($newStatus, ['in_progress', 'completed'])) {
                    abort(403, 'Sebagai staf, Anda hanya boleh mengubah status menjadi Diproses (in_progress) atau Selesai (completed).');
                }
            }
        }

        $validationError = null;

        DB::transaction(function () use (&$validated, &$validationError, $mail, $oldStatus, $newStatus) {
            $docType = \App\Models\DocumentType::with('unit')->findOrFail($validated['document_type_id']);
            $unitId  = $docType->unit_id;
            $validated['unit_id'] = $unitId;

            $tanggalSurat = $validated['tanggal_surat'];
            $year = \Carbon\Carbon::parse($tanggalSurat)->year;

            // Sequence assignment with lock to prevent race conditions
            if (empty($validated['sequence_number'])) {
                $oldYear = \Carbon\Carbon::parse($mail->tanggal_surat)->year;
                if ($oldYear == $year && $mail->unit_id == $unitId) {
                    $validated['sequence_number'] = $mail->sequence_number;
                } else {
                    $maxSeq = Mail::where('unit_id', $unitId)
                        ->whereYear('tanggal_surat', $year)
                        ->where('id', '!=', $mail->id)
                        ->lockForUpdate()
                        ->max('sequence_number') ?? 0;
                    $validated['sequence_number'] = $maxSeq + 1;
                }
            } else {
                $collision = Mail::whereYear('tanggal_surat', $year)
                    ->where('document_type_id', $docType->id)
                    ->where('sequence_number', $validated['sequence_number'])
                    ->where('id', '!=', $mail->id)
                    ->lockForUpdate()
                    ->exists();
                if ($collision) {
                    $validationError = ['sequence_number' => 'Nomor urut kategori tersebut sudah digunakan untuk jenis dokumen ini pada tahun yang sama.'];
                    return;
                }
            }

            // Set is_backdated automatically
            $latestMail = Mail::where('unit_id', $unitId)
                ->where('id', '!=', $mail->id)
                ->latest('tanggal_surat')
                ->first();
            $isBackdated = $latestMail && \Carbon\Carbon::parse($tanggalSurat)->lt($latestMail->tanggal_surat);
            $validated['is_backdated'] = $isBackdated;

            // Verify timeline sequence validation if backdated
            if ($isBackdated) {
                if (!$this->validateBackdateSequence($unitId, $tanggalSurat, $validated['sequence_number'], $mail->id)) {
                    $validationError = ['sequence_number' => 'Nomor urut kategori tidak cocok dengan urutan kronologis tanggal atau tidak mengisi slot kosong yang valid.'];
                    return;
                }
            }

            // Regenerate reference number using LetterFormat
            $format = \App\Models\LetterFormat::where('type', $mail->type)->first();
            $formatString = $format ? $format->format_string : '[NO_URUT]/[KODE_UNIT]/STAI-JIC/[BULAN_ROMAWI]/[TAHUN]';
            $monthStr = \Carbon\Carbon::parse($tanggalSurat)->format('m');
            $romanMonth = self::ROMAN_MONTHS[$monthStr] ?? $monthStr;

            $validated['reference_number'] = str_replace(
                ['[NO_URUT]', '[KODE_UNIT]', '[BULAN_ROMAWI]', '[TAHUN]'],
                [str_pad($validated['sequence_number'], 3, '0', STR_PAD_LEFT), $docType->unit->code, $romanMonth, $year],
                $formatString
            );

            $mail->update($validated);
        });

        if ($validationError) {
            return back()->withInput()->withErrors($validationError);
        }

        if ($oldStatus !== $newStatus) {
            $notifier = Auth::user();
            $notification = new \App\Notifications\MailStatusChanged($mail, $notifier, $oldStatus);
            $usersToNotify = collect([$mail->creator, $mail->assignee])
                ->filter()
                ->reject(fn($u) => $u->id === $notifier->id)
                ->unique('id');
            foreach ($usersToNotify as $u) {
                $u->notify($notification);
            }
        }

        $route = 'mails.' . $mail->type . '.show';
        return redirect()->route($route, $mail)->with('success', 'Surat berhasil diperbarui.');
    }

    public function destroy(Mail $mail)
    {
        $this->authorize('delete', $mail);

        if ($mail->attachment_path) {
            Storage::disk('public')->delete($mail->attachment_path);
        }
        Mail::destroy($mail->id);

        $type = $mail->type;
        return redirect()->route('mails.' . $type . '.index')
            ->with('success', 'Surat berhasil dihapus.');
    }

    public function updateStatus(Request $request, Mail $mail)
    {
        $this->authorize('updateStatus', $mail);

        $request->validate(['status' => 'required|in:draft,pending,in_progress,completed,archived']);
        
        $oldStatus = $mail->status;
        $newStatus = $request->status;
        
        if ($oldStatus !== $newStatus) {
            $user = Auth::user();
            
            // Check State Machine transition
            $allowed = [
                'draft'       => ['pending'],
                'pending'     => ['in_progress', 'draft'],
                'in_progress' => ['completed'],
                'completed'   => ['archived'],
                'archived'    => [],
            ];
            
            if (!in_array($newStatus, $allowed[$oldStatus] ?? [])) {
                return back()->withErrors(['status' => 'Transisi status tidak diperbolehkan.']);
            }
            
            // Check Permissions
            if (!$user->isManager() && !$user->isAdmin()) {
                $isAssignee = ($mail->assigned_to == $user->id) || \App\Models\Disposition::where('mail_id', $mail->id)
                    ->where('to_user_id', $user->id)
                    ->where('status', 'in_progress')
                    ->exists();
                $isCreator = $mail->created_by == $user->id;
                
                if (!$isCreator && !$isAssignee) {
                    abort(403, 'Anda tidak memiliki hak untuk mengubah status dokumen ini.');
                }
                
                if (!in_array($newStatus, ['in_progress', 'completed'])) {
                    abort(403, 'Sebagai staf, Anda hanya boleh mengubah status menjadi Diproses (in_progress) atau Selesai (completed).');
                }
            }
            
            $mail->update(['status' => $newStatus]);
            
            $notifier = Auth::user();
            $notification = new \App\Notifications\MailStatusChanged($mail, $notifier, $oldStatus);
            $usersToNotify = collect([$mail->creator, $mail->assignee])
                ->filter()
                ->reject(fn($u) => $u->id === $notifier->id)
                ->unique('id');
            foreach ($usersToNotify as $u) {
                $u->notify($notification);
            }
        }
        
        return back()->with('success', 'Status surat berhasil diperbarui.');
    }

    public function updateReferenceNumber(Request $request, Mail $mail)
    {
        $this->authorize('updateReferenceNumber', $mail);

        $validated = $request->validate([
            'reference_number' => 'required|string|max:50|unique:mails,reference_number,'.$mail->id,
            'change_reason'    => 'required|string|max:255',
        ]);

        $oldRef = $mail->reference_number;
        $mail->update(['reference_number' => $validated['reference_number']]);

        MailReferenceLog::create([
            'mail_id'       => $mail->id,
            'changed_by'    => Auth::id(),
            'old_reference' => $oldRef,
            'new_reference' => $validated['reference_number'],
            'reason'        => $validated['change_reason'],
        ]);

        return back()->with('success', 'Nomor referensi berhasil diperbarui.');
    }

    // ─── ARCHIVE ─────────────────────────────────────────────────────────────

    public function indexArchive(Request $request)
    {
        $this->authorize('viewAny', Mail::class);

        $query = Mail::with('creator', 'assignee');
        
        // Extra archive filters
        if ($year = $request->year) {
            $query->whereYear('tanggal_surat', '=', $year, 'and');
        }
        if ($month = $request->month) {
            $query->whereMonth('tanggal_surat', '=', $month, 'and');
        }
        if ($type = $request->type) {
            $query->where('type', '=', $type);
        }
        if ($classification = $request->classification) {
            $query->where('classification', '=', $classification);
        }

        return $this->applyFilters($query, $request, 'mails.archive.index');
    }

    public function exportArchive(Request $request)
    {
        $this->authorize('exportArchive', Mail::class);

        // Placeholder for Excel Export, returning a simple CSV stream.
        // If Maatwebsite/Excel is installed, they can replace this with proper export.
        $query = Mail::where('status', '=', 'archived', 'and')
            ->select(['id', 'reference_number', 'type', 'subject', 'sender_name', 'recipient_name', 'tanggal_surat', 'status']);
            
        // Simple manual filter parsing...
        if ($request->year) $query->whereYear('tanggal_surat', '=', $request->year, 'and');
        if ($request->month) $query->whereMonth('tanggal_surat', '=', $request->month, 'and');
        if ($request->type) $query->where('type', '=', $request->type);
        if ($request->classification) $query->where('classification', '=', $request->classification);
        
        $filename = "arsip-surat-" . date('Ymd') . ".csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use($query) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['No Referensi', 'Tipe', 'Perihal', 'Pengirim', 'Penerima', 'Tanggal Surat', 'Status']);
            
            // Stream in chunks of 500 to keep memory overhead to an absolute minimum
            $query->chunk(500, function ($mails) use ($file) {
                foreach ($mails as $mail) {
                    fputcsv($file, [
                        $mail->reference_number,
                        $mail->type_label,
                        $mail->subject,
                        $mail->sender_name,
                        $mail->recipient_name,
                        optional($mail->tanggal_surat)->format('Y-m-d'),
                        $mail->status
                    ]);
                }
            });
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportArchivePdf(Request $request)
    {
        $this->authorize('exportArchive', Mail::class);

        // Placeholder for PDF. Since dompdf might not be loaded, 
        // return an error if class 'Barryvdh\DomPDF\Facade\Pdf' doesn't exist,
        // or just return simple text.
        if (!class_exists('\Barryvdh\DomPDF\Facade\Pdf')) {
            return back()->with('error', 'Package DOMPDF belum terinstall. Jalankan composer require barryvdh/laravel-dompdf');
        }
        
        $query = Mail::where('status', '=', 'archived', 'and')
            ->select(['id', 'reference_number', 'type', 'subject', 'sender_name', 'recipient_name', 'tanggal_surat', 'status']);
            
        if ($request->year) $query->whereYear('tanggal_surat', '=', $request->year, 'and');
        if ($request->month) $query->whereMonth('tanggal_surat', '=', $request->month, 'and');
        if ($request->type) $query->where('type', '=', $request->type);
        if ($request->classification) $query->where('classification', '=', $request->classification);
        
        $mails = $query->get();
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('mails.archive.pdf', compact('mails'));
        return $pdf->download('arsip-surat.pdf');
    }

    /**
     * Validate that a backdated letter's sequence_number fits correctly into the
     * historical timeline (i.e., slot is vacant and chronologically ordered).
     */
    private function validateBackdateSequence(int $unitId, string $tanggalSurat, int $seqNumber, ?int $excludeMailId = null): bool
    {
        $year = \Carbon\Carbon::parse($tanggalSurat)->year;
        
        $prevMail = Mail::where('unit_id', $unitId)
            ->whereYear('tanggal_surat', $year)
            ->where('sequence_number', '<', $seqNumber)
            ->when($excludeMailId, fn($q) => $q->where('id', '!=', $excludeMailId))
            ->orderByDesc('sequence_number')
            ->first();

        $nextMail = Mail::where('unit_id', $unitId)
            ->whereYear('tanggal_surat', $year)
            ->where('sequence_number', '>', $seqNumber)
            ->when($excludeMailId, fn($q) => $q->where('id', '!=', $excludeMailId))
            ->orderBy('sequence_number')
            ->first();

        $targetDate = \Carbon\Carbon::parse($tanggalSurat);

        return (new \App\Services\ChronologicalGuard())->validate($targetDate, $prevMail, $nextMail);
    }
}
