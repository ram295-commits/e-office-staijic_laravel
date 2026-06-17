<?php

namespace App\Http\Controllers;

use App\Models\Mail;
use App\Models\LetterFormat;
use App\Models\LetterNumberResetRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LetterNumberController extends Controller
{
    public function index()
    {
        $resetRequests = LetterNumberResetRequest::with('requester', 'approver')->orderBy('created_at', 'desc')->get();
        return view('administrasi.nomor-surat.index', compact('resetRequests'));
    }

    // FEATURE 1: GLOBAL LETTER FORMAT SETTINGS
    public function formatSettings()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->isAdmin()) {
            abort(403, 'Akses Ditolak: Hanya Administrator yang dapat mengatur format nomor surat.');
        }
        $formats = LetterFormat::all()->keyBy('type');
        return view('administrasi.nomor-surat.format', compact('formats'));
    }

    public function updateFormatSettings(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'formats.incoming' => 'required|string',
            'formats.outgoing' => 'required|string',
            'formats.internal' => 'required|string',
        ]);

        foreach ($request->formats as $type => $format_string) {
            LetterFormat::updateOrCreate(
                ['type' => $type],
                ['format_string' => $format_string]
            );
        }

        return back()->with('success', 'Format penomoran surat berhasil disimpan.');
    }

    // FEATURE 2: STRICT LETTER REVISION
    public function revisiForm(int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->isAdmin() && !$user->isManager()) {
            abort(403, 'Akses Ditolak.');
        }
        $mail = Mail::findOrFail($id);
        return view('administrasi.nomor-surat.revisi', compact('mail'));
    }

    public function updateRevisi(Request $request, int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->isAdmin() && !$user->isManager()) {
            abort(403);
        }

        $request->validate([
            'sequence_number' => 'required|integer|min:1',
            'sender_unit' => 'required|string',
            'type' => 'required|in:incoming,outgoing,internal',
            'tanggal_surat' => 'required|date',
            'change_reason' => 'required|string',
        ]);

        $mail = Mail::findOrFail($id);

        // Check for duplicates
        $year = date('Y', strtotime($request->tanggal_surat));
        $duplicate = Mail::where('type', '=', $request->type, 'and')
            ->where('sequence_number', '=', $request->sequence_number, 'and')
            ->whereYear('tanggal_surat', $year)
            ->where('id', '!=', $mail->id)
            ->exists();
            
        if ($duplicate) {
            return back()->withInput()->with('error', 'Nomor urut '.$request->sequence_number.' sudah digunakan pada tahun dan jenis surat yang sama.');
        }

        $format = LetterFormat::where('type', '=', $request->type, 'and')->first();
        $formatString = $format ? $format->format_string : '[NO_URUT]/[KODE_UNIT]/STAI-JIC/[BULAN_ROMAWI]/[TAHUN]';

        $romans = ['01'=>'I','02'=>'II','03'=>'III','04'=>'IV','05'=>'V','06'=>'VI','07'=>'VII','08'=>'VIII','09'=>'IX','10'=>'X','11'=>'XI','12'=>'XII'];
        $monthStr = date('m', strtotime($request->tanggal_surat));
        $romanMonth = $romans[$monthStr] ?? $monthStr;

        $newRef = str_replace(
            ['[NO_URUT]', '[KODE_UNIT]', '[BULAN_ROMAWI]', '[TAHUN]'],
            [str_pad($request->sequence_number, 3, '0', STR_PAD_LEFT), $request->sender_unit, $romanMonth, $year],
            $formatString
        );

        $oldRef = $mail->reference_number;

        \App\Models\MailReferenceLog::create([
            'mail_id' => $mail->id,
            'changed_by' => Auth::id(),
            'old_reference' => $oldRef,
            'new_reference' => $newRef,
            'reason' => $request->change_reason,
        ]);

        $mail->update([
            'reference_number' => $newRef,
            'sequence_number' => $request->sequence_number,
            'sender_unit' => $request->sender_unit,
            'type' => $request->type,
            'tanggal_surat' => $request->tanggal_surat,
        ]);

        return redirect()->route('mails.archive.index')->with('success', 'Surat berhasil direvisi dengan nomor: ' . $newRef);
    }

    public function requestReset(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->isAdmin()) {
            abort(403, 'Akses Ditolak: Hanya Admin yang dapat mengajukan.');
        }
        
        $targetYear = date('Y');
        
        $exists = LetterNumberResetRequest::where('status', '=', 'pending', 'and')->exists();
        if ($exists) {
            return back()->with('error', 'Sudah ada permintaan reset yang sedang menunggu persetujuan.');
        }

        LetterNumberResetRequest::create([
            'requested_by' => Auth::id(),
            'target_year' => $targetYear,
            'status' => 'pending',
            'notes' => $request->notes,
        ]);

        return back()->with('success', 'Permintaan reset nomor surat berhasil diajukan. Menunggu persetujuan Kepala Unit/Manager.');
    }

    public function approveReset(Request $request, int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->isManager()) {
            abort(403, 'Akses Ditolak: Hanya Kepala Unit/Manager yang dapat memberikan persetujuan.');
        }

        $resetReq = LetterNumberResetRequest::findOrFail($id);
        
        if ($request->action === 'reject') {
            $resetReq->update([
                'status' => 'rejected',
                'approved_by' => Auth::id(),
            ]);
            return back()->with('success', 'Permintaan reset ditolak.');
        }

        // Approve action
        $resetReq->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
        ]);

        return back()->with('success', 'Permintaan reset disetujui. Sequence nomor surat telah di-reset (secara logika sistem akan memulai dari 1 pada tahun yang relevan).');
    }
}
