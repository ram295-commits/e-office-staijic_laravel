<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DispositionController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// ── Auth ──────────────────────────────────────────────────────────────────────
Route::get('/', fn () => redirect()->route('login'));
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ── Authenticated Routes ───────────────────────────────────────────────────────
Route::middleware(['auth', 'check.active'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [MailController::class, 'dashboard'])->name('dashboard');

    // ── Incoming Mails ───────────────────────────────────────────────────────
    Route::prefix('surat-masuk')->name('mails.incoming.')->group(function () {
        Route::get('/', [MailController::class, 'indexIncoming'])->name('index');
        Route::get('/tambah', [MailController::class, 'createIncoming'])->name('create');
        Route::get('/{mail}', [MailController::class, 'show'])->name('show');
    });

    // ── Outgoing Mails ───────────────────────────────────────────────────────
    Route::prefix('surat-keluar')->name('mails.outgoing.')->group(function () {
        Route::get('/', [MailController::class, 'indexOutgoing'])->name('index');
        Route::get('/tambah', [MailController::class, 'createOutgoing'])->name('create');
        Route::get('/{mail}', [MailController::class, 'show'])->name('show');
    });

    // ── Internal Mails ───────────────────────────────────────────────────────
    Route::prefix('surat-internal')->name('mails.internal.')->group(function () {
        Route::get('/', [MailController::class, 'indexInternal'])->name('index');
        Route::get('/tambah', [MailController::class, 'createInternal'])->name('create');
        Route::get('/{mail}', [MailController::class, 'show'])->name('show');
    });

    // ── Shared Mail Routes ───────────────────────────────────────────────────
    Route::post('/surat', [MailController::class, 'store'])->name('mails.store');
    Route::get('/surat/{mail}', [MailController::class, 'show'])->name('mails.show');
    Route::get('/surat/{mail}/edit', [MailController::class, 'edit'])->name('mails.edit');
    Route::put('/surat/{mail}', [MailController::class, 'update'])->name('mails.update');
    Route::delete('/surat/{mail}', [MailController::class, 'destroy'])->name('mails.destroy');
    Route::patch('/surat/{mail}/status', [MailController::class, 'updateStatus'])->name('mails.status');

    // Route helpers to show by type
    Route::get('/surat/{mail}/view', function (\App\Models\Mail $mail) {
        return redirect()->route('mails.' . $mail->type . '.show', $mail);
    })->name('mails.view');

    // Reference Number logic moved to Administrasi -> Manajemen Nomor Surat

    // Import Routes
    Route::get('/surat-import', [\App\Http\Controllers\MailImportController::class, 'index'])->name('mails.import.index');
    Route::post('/surat-import', [\App\Http\Controllers\MailImportController::class, 'store'])->name('mails.import.store');
    Route::get('/surat-import/template', [\App\Http\Controllers\MailImportController::class, 'downloadTemplate'])->name('mails.import.template');

    // Archive Routes
    Route::prefix('arsip')->name('mails.archive.')->group(function () {
        Route::get('/', [MailController::class, 'indexArchive'])->name('index');
        Route::get('/export', [MailController::class, 'exportArchive'])->name('export');
        Route::get('/export-pdf', [MailController::class, 'exportArchivePdf'])->name('export-pdf');
    });

    // ── Dispositions ─────────────────────────────────────────────────────────
    Route::prefix('disposisi')->name('dispositions.')->group(function () {
        Route::get('/', [DispositionController::class, 'index'])->name('index');
        Route::post('/', [DispositionController::class, 'store'])->name('store');
        Route::get('/{disposition}', [DispositionController::class, 'show'])->name('show');
        Route::post('/{disposition}/respond', [DispositionController::class, 'respond'])->name('respond');
        Route::delete('/{disposition}', [DispositionController::class, 'destroy'])->name('destroy');
    });

    // ── Administrasi ─────────────────────────────────────────────────────────
    Route::prefix('administrasi')->name('administrasi.')->group(function () {
        
        // A. Kelola Pengguna
        Route::prefix('pengguna')->name('users.')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::get('/tambah', [UserController::class, 'create'])->name('create');
            Route::post('/', [UserController::class, 'store'])->name('store');
            Route::get('/{user}', [UserController::class, 'show'])->name('show');
            Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
            Route::put('/{user}', [UserController::class, 'update'])->name('update');
            Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
            Route::post('/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('toggle-active');
        });

        // B. Tata Arsip
        Route::get('/tata-arsip', [\App\Http\Controllers\ArchiveSopController::class, 'index'])->name('tata-arsip.index');
        Route::put('/tata-arsip/update', [\App\Http\Controllers\ArchiveSopController::class, 'updateSop'])->name('tata-arsip.update-sop');
        
        // C. Manajemen Nomor Surat
        Route::prefix('nomor-surat')->name('nomor-surat.')->group(function () {
            Route::get('/', [\App\Http\Controllers\LetterNumberController::class, 'index'])->name('index');
            Route::get('/format', [\App\Http\Controllers\LetterNumberController::class, 'formatSettings'])->name('format-settings');
            Route::put('/format', [\App\Http\Controllers\LetterNumberController::class, 'updateFormatSettings'])->name('update-format');
            Route::get('/revisi/{id}', [\App\Http\Controllers\LetterNumberController::class, 'revisiForm'])->name('revisi-form');
            Route::patch('/revisi/{id}', [\App\Http\Controllers\LetterNumberController::class, 'updateRevisi'])->name('update-revisi');
            Route::post('/reset/request', [\App\Http\Controllers\LetterNumberController::class, 'requestReset'])->name('reset.request');
            Route::post('/reset/approve/{id}', [\App\Http\Controllers\LetterNumberController::class, 'approveReset'])->name('reset.approve');
        });

        // D. Master Data
        Route::group([], function () {
            Route::resource('units', \App\Http\Controllers\Administrasi\UnitController::class)->except('show');
            Route::resource('document-types', \App\Http\Controllers\Administrasi\DocumentTypeController::class)->except('show')->parameters([
                'document-types' => 'documentType'
            ]);
        });
    });

    // ── Number Reservations ──────────────────────────────────────────────────
    Route::get('/reservasi', [\App\Http\Controllers\NumberReservationController::class, 'index'])->name('number_reservations.index');
    Route::get('/reservasi/tambah', [\App\Http\Controllers\NumberReservationController::class, 'create'])->name('number_reservations.create');
    Route::post('/reservasi', [\App\Http\Controllers\NumberReservationController::class, 'store'])->name('number_reservations.store');
    Route::post('/reservasi/{id}/approve', [\App\Http\Controllers\NumberReservationController::class, 'approve'])->name('number_reservations.approve');
    Route::post('/reservasi/{id}/reject', [\App\Http\Controllers\NumberReservationController::class, 'reject'])->name('number_reservations.reject');
    Route::get('/reservasi/{reservation}/fill/{mail}', [\App\Http\Controllers\NumberReservationController::class, 'showFillSlot'])->name('number_reservations.fill_slot.show');
    Route::put('/reservasi/{reservation}/fill/{mail}', [\App\Http\Controllers\NumberReservationController::class, 'fillSlot'])->name('number_reservations.fill_slot');

    // ── E2E Route Aliases (UAT Workflow) ─────────────────────────────────────
    Route::post('/reservasi/submit', [\App\Http\Controllers\NumberReservationController::class, 'store'])->name('reservations.store');
    Route::post('/reservasi/approve-request/{id}', [\App\Http\Controllers\NumberReservationController::class, 'approve'])->name('reservations.approve');
    Route::put('/reservasi/{reservation}/fill-slot/{slotIndex}', function(\Illuminate\Http\Request $request, $reservationId, $slotIndex) {
        $reservation = \App\Models\NumberReservation::findOrFail($reservationId);
        $slots = $reservation->reserved_slots;
        $mailId = $slots[$slotIndex]['mail_id'] ?? null;
        if (!$mailId) {
            abort(404, 'Slot index not found.');
        }
        return app(\App\Http\Controllers\NumberReservationController::class)->fillSlot($request, $reservationId, $mailId);
    })->name('reservations.fill-slot');

    // Android UI/UX Simulator
    Route::get('/android-ui', function() {
        return view('android-ui');
    })->name('android-ui');
});

