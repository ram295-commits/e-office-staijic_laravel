<?php

namespace App\Http\Controllers;

use App\Models\NumberReservation;
use App\Models\DocumentType;
use App\Models\LetterFormat;
use App\Models\Mail;
use App\Models\User;
use App\Services\ChronologicalGuard;
use App\Notifications\NumberReservationRequested;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class NumberReservationController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', NumberReservation::class);

        $status = $request->get('status', 'pending');
        $reservations = NumberReservation::with(['requester', 'approver', 'documentType', 'letterFormat'])
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('number_reservations.index', compact('reservations', 'status'));
    }

    public function create()
    {
        Gate::authorize('create', NumberReservation::class);

        $documentTypes = DocumentType::with('unit')->get();
        $letterFormats = LetterFormat::all();

        return view('number_reservations.create', compact('documentTypes', 'letterFormats'));
    }


    public function store(Request $request)
    {
        Gate::authorize('create', NumberReservation::class);

        $validated = $request->validate([
            'letter_format_id' => 'required|exists:letter_formats,id',
            'document_type_id' => 'required|exists:document_types,id',
            'quantity'         => 'required|integer|min:1',
            'backdate_target'  => 'required|date',
            'reason'           => 'required|string',
        ]);

        $reservation = NumberReservation::create([
            'letter_format_id' => $validated['letter_format_id'],
            'document_type_id' => $validated['document_type_id'],
            'requested_by'     => Auth::id(),
            'approved_by'      => null,
            'quantity'         => $validated['quantity'],
            'status'           => 'pending',
            'reserved_slots'   => [],
            'backdate_target'  => $validated['backdate_target'],
            'reason'           => $validated['reason'],
        ]);

        // Dispatch queued notification directly to admins
        $admins = User::where('role', 'admin')->get();
        Notification::send($admins, new NumberReservationRequested($reservation));

        return redirect()->back()->with('success', 'Permintaan reservasi nomor berhasil dibuat.');
    }

    public function approve(Request $request, $id)
    {
        $reservation = NumberReservation::findOrFail($id);

        Gate::authorize('approve', $reservation);

        if ($reservation->status !== 'pending') {
            return redirect()->back()->with('error', 'Permintaan reservasi ini tidak dalam status pending.');
        }

        $docType = DocumentType::with('unit')->findOrFail($reservation->document_type_id);
        $unitId = $docType->unit_id;
        $targetDate = Carbon::parse($reservation->backdate_target);
        $year = $targetDate->year;

        $createdMails = DB::transaction(function () use ($reservation, $docType, $unitId, $targetDate, $year) {
            // lockForUpdate on existing mails of the same unit/year to avoid race conditions on sequence allocation
            Mail::where('unit_id', $unitId)
                ->whereYear('tanggal_surat', $year)
                ->lockForUpdate()
                ->get();

            // Find the mail with the largest sequence number whose date is <= targetDate
            $prevMail = Mail::where('unit_id', $unitId)
                ->whereYear('tanggal_surat', $year)
                ->where('tanggal_surat', '<=', $targetDate)
                ->orderByDesc('sequence_number')
                ->first();

            $startSequence = $prevMail ? $prevMail->sequence_number + 1 : 1;

            // Get all occupied sequence numbers for the document_type_id in the target year >= startSequence
            $occupiedSequences = Mail::whereYear('tanggal_surat', $year)
                ->where('document_type_id', $reservation->document_type_id)
                ->where('sequence_number', '>=', $startSequence)
                ->pluck('sequence_number')
                ->toArray();

            // Find N vacant consecutive sequence numbers starting from $startSequence
            $candidate = $startSequence;
            while (true) {
                $conflict = false;
                for ($i = 0; $i < $reservation->quantity; $i++) {
                    if (in_array($candidate + $i, $occupiedSequences)) {
                        $conflict = true;
                        break;
                    }
                }
                if (!$conflict) {
                    break;
                }
                $candidate++;
            }

            // Validate via ChronologicalGuard
            $guard = new ChronologicalGuard();
            $isValid = $guard->validateForReservation(
                $targetDate,
                $unitId,
                $candidate,
                $reservation->quantity
            );

            if (!$isValid) {
                throw ValidationException::withMessages([
                    'backdate_target' => 'Reservasi nomor tidak memenuhi aturan kronologis tanggal (Safe Backdate Violation).',
                ]);
            }

            // Generate format details
            $format = LetterFormat::findOrFail($reservation->letter_format_id);
            $formatString = $format->format_string;
            $romans = ['01'=>'I','02'=>'II','03'=>'III','04'=>'IV','05'=>'V','06'=>'VI','07'=>'VII','08'=>'VIII','09'=>'IX','10'=>'X','11'=>'XI','12'=>'XII'];
            $monthStr = $targetDate->format('m');
            $romanMonth = $romans[$monthStr] ?? $monthStr;

            $mailsCreated = [];

            // Calculate is_backdated dynamically
            $latestMail = Mail::where('unit_id', $unitId)->latest('tanggal_surat')->first();
            $isBackdated = $latestMail && $targetDate->lt($latestMail->tanggal_surat);

            for ($i = 0; $i < $reservation->quantity; $i++) {
                $seq = $candidate + $i;

                $referenceNumber = str_replace(
                    ['[NO_URUT]', '[KODE_UNIT]', '[BULAN_ROMAWI]', '[TAHUN]'],
                    [str_pad($seq, 3, '0', STR_PAD_LEFT), $docType->unit->code, $romanMonth, $year],
                    $formatString
                );

                $mail = Mail::create([
                    'reference_number'    => $referenceNumber,
                    'type'                => $format->type ?? 'incoming',
                    'document_type_id'    => $reservation->document_type_id,
                    'unit_id'             => $unitId,
                    'sequence_number'     => $seq,
                    'sender_name'         => '[Reserved]',
                    'recipient_name'      => '[Reserved]',
                    'subject'             => '[Reserved Slot]',
                    'body'                => '[Reserved Slot Body]',
                    'tanggal_surat'       => $targetDate,
                    'status'              => 'draft',
                    'is_backdated'        => $isBackdated,
                    'created_by'          => $reservation->requested_by,
                    'reservation_slot_id' => $reservation->id,
                    'date_locked'         => true,
                ]);

                $mailsCreated[] = [
                    'mail_id'         => $mail->id,
                    'sequence_number' => $mail->sequence_number,
                    'date'            => $mail->tanggal_surat->format('Y-m-d'),
                ];
            }

            $reservation->update([
                'status'         => 'approved',
                'approved_by'    => Auth::id(),
                'reserved_slots' => $mailsCreated,
            ]);

            return $mailsCreated;
        });

        return redirect()->back()->with('success', 'Permintaan reservasi nomor disetujui. ' . count($createdMails) . ' slot berhasil dibuat.');
    }

    public function fillSlot(Request $request, $reservationId, $mailId)
    {
        $reservation = NumberReservation::findOrFail($reservationId);

        Gate::authorize('fillSlot', $reservation);

        $mail = Mail::where('reservation_slot_id', $reservation->id)->findOrFail($mailId);

        // Validation rule: date field is immutable after reservation
        // If tanggal_surat is sent in request and doesn't match the locked date, reject it
        if ($request->has('tanggal_surat') && Carbon::parse($request->tanggal_surat)->format('Y-m-d') !== $mail->tanggal_surat->format('Y-m-d')) {
            throw ValidationException::withMessages([
                'tanggal_surat' => 'Tanggal surat pada slot yang telah direservasi tidak dapat diubah.',
            ]);
        }

        $validated = $request->validate([
            'subject'              => 'required|string|max:255',
            'body'                 => 'required|string',
            'sender_name'          => 'required|string|max:150',
            'sender_organization'  => 'nullable|string|max:150',
            'sender_email'         => 'nullable|email|max:150',
            'recipient_name'       => 'required|string|max:150',
            'recipient_department' => 'nullable|string|max:150',
            'recipient_email'      => 'nullable|email|max:150',
            'priority'             => 'required|in:normal,urgent,very_urgent',
            'classification'       => 'required|in:open,confidential,secret',
            'notes'                => 'nullable|string',
        ]);

        // Keep date_locked as true as per instructions, and prevent date update
        $mail->update(array_merge($validated, [
            'status' => 'pending', // update status to pending
        ]));

        return redirect()->route('number_reservations.index')->with('success', 'Slot reservasi nomor berhasil diisi.');
    }

    public function reject(Request $request, $id)
    {
        $reservation = NumberReservation::findOrFail($id);
        Gate::authorize('approve', $reservation);

        if ($reservation->status !== 'pending') {
            return redirect()->back()->with('error', 'Permintaan reservasi ini tidak dalam status pending.');
        }

        $request->validate([
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $reservation->update([
            'status'      => 'rejected',
            'approved_by' => Auth::id(),
            'reason'      => $request->rejection_reason ?? $reservation->reason,
        ]);

        return redirect()->back()->with('success', 'Permintaan reservasi nomor telah ditolak.');
    }

    public function showFillSlot($reservationId, $mailId)
    {
        $reservation = NumberReservation::with(['documentType', 'letterFormat', 'requester'])->findOrFail($reservationId);
        Gate::authorize('fillSlot', $reservation);

        $mail = Mail::where('reservation_slot_id', $reservation->id)->findOrFail($mailId);

        return view('number_reservations.fill', compact('reservation', 'mail'));
    }
}
