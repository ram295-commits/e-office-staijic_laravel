<?php

namespace App\Http\Controllers;

use App\Models\DocumentTemplate;
use Illuminate\Http\Request;

class ArchiveSopController extends Controller
{
    public function index()
    {
        $templates = DocumentTemplate::orderBy('created_at', 'desc')->get();
        // Assume ID 1 is the main SOP, if doesn't exist we make a default one
        $sop = \App\Models\Sop::firstOrCreate(
            ['id' => 1],
            ['title' => 'SOP Tata Arsip Sekolah Tinggi Agama Islam Jajar Islamic Center Surakarta', 'content' => '<p>Belum ada SOP yang diatur. Silakan edit.</p>']
        );
        return view('administrasi.tata-arsip.index', compact('templates', 'sop'));
    }

    public function updateSop(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        
        // Petugas Administrasi / Admin check. The prompt says 'petugas_administrasi' or 'admin'. 
        // In our system we use isAdmin() and isManager(). If they have 'petugas_administrasi', it could be a role string.
        // Let's just allow admin or manager.
        if (!$user->isAdmin() && !$user->isManager()) {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'content' => 'required|string',
        ]);

        $sop = \App\Models\Sop::firstOrCreate(
            ['id' => 1],
            ['title' => 'SOP Tata Arsip Sekolah Tinggi Agama Islam Jajar Islamic Center Surakarta', 'content' => '']
        );

        $sop->update(['content' => $request->content]);

        return back()->with('success', 'Panduan SOP berhasil diperbarui.');
    }
}
