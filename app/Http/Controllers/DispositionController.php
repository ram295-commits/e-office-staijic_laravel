<?php

namespace App\Http\Controllers;

use App\Models\Disposition;
use App\Models\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class DispositionController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $received = Disposition::with(['mail', 'fromUser'])
            ->where('to_user_id', $user->id)
            ->latest()
            ->paginate(15, ['*'], 'received_page');

        $sent = Disposition::with(['mail', 'toUser'])
            ->where('from_user_id', $user->id)
            ->latest()
            ->paginate(15, ['*'], 'sent_page');

        return view('dispositions.index', compact('received', 'sent'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'mail_id'      => 'required|exists:mails,id',
            'to_user_id'   => 'nullable|required_without:to_user_ids|exists:users,id',
            'to_user_ids'  => 'nullable|required_without:to_user_id|array',
            'to_user_ids.*'=> 'exists:users,id',
            'instruction'  => 'required|string|max:1000',
            'action_type'  => 'required|in:for_review,for_action,for_information,for_approval,for_filing,for_reply,coordinate,other',
            'due_date'     => 'nullable|date|after:today',
        ]);

        $recipientIds = [];
        if ($request->has('to_user_ids') && is_array($request->to_user_ids)) {
            $recipientIds = $request->to_user_ids;
        } elseif ($request->has('to_user_id')) {
            $recipientIds = [$request->to_user_id];
        }

        // Update mail status to in_progress when disposed
        Mail::find($validated['mail_id'])->update(['status' => 'in_progress']);

        $createdCount = 0;
        $lastRecipientName = '';

        foreach ($recipientIds as $recipientId) {
            $disp = Disposition::create([
                'mail_id'      => $validated['mail_id'],
                'from_user_id' => Auth::id(),
                'to_user_id'   => $recipientId,
                'instruction'  => $validated['instruction'],
                'action_type'  => $validated['action_type'],
                'due_date'     => $validated['due_date'] ?? null,
                'status'       => 'pending',
            ]);
            $createdCount++;

            // Send notification to the recipient (toUser)
            $recipient = \App\Models\User::find($recipientId);
            if ($recipient) {
                $recipient->notify(new \App\Notifications\DispositionCreated($disp));
                $lastRecipientName = $recipient->name;
            }
        }

        $successMsg = $createdCount > 1 
            ? 'Disposisi berhasil dikirim kepada ' . $createdCount . ' penerima.'
            : 'Disposisi berhasil dikirim kepada ' . $lastRecipientName;

        return back()->with('success', $successMsg);
    }

    public function show(Disposition $disposition)
    {
        $user = Auth::user();

        // Mark as read if recipient
        if ($disposition->to_user_id === $user->id && !$disposition->read_at) {
            $disposition->update(['read_at' => now()]);
        }

        $disposition->load('mail.creator', 'fromUser', 'toUser');
        return view('dispositions.show', compact('disposition'));
    }

    public function respond(Request $request, Disposition $disposition)
    {
        // Only the recipient can respond
        Gate::authorize('respond', $disposition);

        $request->validate([
            'response_notes'      => 'required|string|max:2000',
            'status'              => 'required|in:in_progress,completed',
            'response_attachment' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png',
        ]);

        $updateData = [
            'response_notes' => $request->response_notes,
            'status'         => $request->status,
            'responded_at'   => now(),
        ];

        if ($request->hasFile('response_attachment')) {
            // Delete old file if exists
            if ($disposition->response_attachment_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($disposition->response_attachment_path);
            }
            $file = $request->file('response_attachment');
            $updateData['response_attachment_path'] = $file->store('disposition-responses', 'public');
            $updateData['response_attachment_name'] = $file->getClientOriginalName();
        }

        $disposition->update($updateData);

        // If all dispositions completed, mark mail as completed
        $mail = $disposition->mail;
        $allDone = $mail->dispositions()->where('status', '!=', 'completed')->count() === 0;
        if ($allDone && $mail->dispositions()->count() > 0) {
            $mail->update(['status' => 'completed']);
        }

        return back()->with('success', 'Respon disposisi berhasil disimpan.');
    }

    public function destroy(Disposition $disposition)
    {
        // Only the sender can cancel a pending disposition
        Gate::authorize('delete', $disposition);
        $disposition->update(['status' => 'cancelled']);
        return back()->with('success', 'Disposisi dibatalkan.');
    }
}
