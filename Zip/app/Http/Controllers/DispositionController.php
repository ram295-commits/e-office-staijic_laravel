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
            'mail_id'     => 'required|exists:mails,id',
            'to_user_id'  => 'required|exists:users,id',
            'instruction' => 'required|string|max:1000',
            'action_type' => 'required|in:for_review,for_action,for_information,for_approval,for_filing,for_reply,coordinate,other',
            'due_date'    => 'nullable|date|after:today',
        ]);

        $validated['from_user_id'] = Auth::id();
        $validated['status'] = 'pending';

        // Update mail status to in_progress when disposed
        Mail::find($validated['mail_id'])->update(['status' => 'in_progress']);

        $disposition = Disposition::create($validated);

        // Send notification to the recipient (toUser)
        $recipient = \App\Models\User::find($disposition->to_user_id);
        if ($recipient) {
            $recipient->notify(new \App\Notifications\DispositionCreated($disposition));
        }

        return back()->with('success', 'Disposisi berhasil dikirim kepada ' . $disposition->toUser->name);
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
            'response_notes' => 'required|string|max:2000',
            'status'         => 'required|in:in_progress,completed',
        ]);

        $disposition->update([
            'response_notes' => $request->response_notes,
            'status'         => $request->status,
            'responded_at'   => now(),
        ]);

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
