<?php

namespace App\Imports;

use App\Models\Mail;
use App\Models\DocumentType;
use App\Models\LetterFormat;
use App\Models\Unit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Carbon\Carbon;

class MailsImport implements ToModel, WithHeadingRow, WithValidation, WithChunkReading
{
    private $sequenceTracker = [];

    // In-memory caches to eliminate N+1 query pattern
    private $documentTypes = null;
    private $letterFormats = null;
    private $latestMailDates = [];
    private $existingReferenceNumbers = null;
    private $existingSequences = null;
    private $unitYearMails = [];
    private $cacheWarmed = false;

    /**
     * Process the file in chunks of 200 rows to keep memory overhead low.
     */
    public function chunkSize(): int
    {
        return 200;
    }


    /**
     * Map each row to a Mail model.
     */
    public function model(array $row)
    {
        // Headers are slugified by WithHeadingRow:
        // tipe, subjek, isi, nama_pengirim, organisasi_pengirim, email_pengirim,
        // nama_penerima, departemen_penerima, email_penerima, tanggal_surat,
        // tanggal_diterima, prioritas, klasifikasi, kode_surat, nomor_urut, unit_pengirim, jenjang, catatan

        // 1. Warm all caches once at the start of the first row
        if (!$this->cacheWarmed) {
            $this->warmAllCaches();
        }

        $kodeSurat = trim($row['kode_surat'] ?? '');
        $docType = $this->documentTypes->get($kodeSurat);
        if (!$docType) {
            throw new \Exception("Kode Surat '{$kodeSurat}' tidak ditemukan di sistem.");
        }

        $unitId = $docType->unit_id;
        $tanggalSuratStr = trim($row['tanggal_surat'] ?? '');
        
        try {
            $tanggalSurat = Carbon::parse($tanggalSuratStr);
        } catch (\Exception $e) {
            throw new \Exception("Format Tanggal Surat '{$tanggalSuratStr}' tidak valid.");
        }
        
        $year = $tanggalSurat->year;

        // Auto-assign sequence_number if not provided
        $seqKey = "{$unitId}_{$year}";
        $sequenceNumber = !empty($row['nomor_urut']) ? intval($row['nomor_urut']) : null;

        if ($sequenceNumber === null) {
            // sequenceTracker is pre-seeded from DB max in warmAllCaches()
            if (!isset($this->sequenceTracker[$seqKey])) {
                $this->sequenceTracker[$seqKey] = 0;
            }

            $nextSeq = $this->sequenceTracker[$seqKey] + 1;
            $sequenceNumber = $nextSeq;
            $this->sequenceTracker[$seqKey] = $nextSeq;
        } else {

            // Update tracker with the maximum sequence seen
            $this->sequenceTracker[$seqKey] = max($this->sequenceTracker[$seqKey] ?? 0, $sequenceNumber);

            // Check composite collision in-memory: (Tahun, KodeSurat, Urut Kategori)
            $colKey = "{$year}_{$docType->id}_{$sequenceNumber}";
            if (isset($this->existingSequences[$colKey])) {
                throw new \Exception("Nomor urut '{$sequenceNumber}' untuk Jenis Dokumen '{$docType->name}' pada tahun {$year} sudah digunakan di database.");
            }

            // Register sequence in-memory cache to prevent collision with subsequent rows in same sheet
            $this->existingSequences[$colKey] = true;
        }

        // Set is_backdated automatically (uses pre-warmed cache — no per-row query)
        $latestMailDate = $this->latestMailDates[$unitId] ?? null;
        $isBackdated = $latestMailDate && $tanggalSurat->lt($latestMailDate);

        // Verify timeline sequence validation if backdated
        if ($isBackdated) {
            if (!$this->validateBackdateSequenceInMemory($unitId, $tanggalSurat, $sequenceNumber)) {
                throw new \Exception("Nomor urut '{$sequenceNumber}' untuk tanggal '{$tanggalSurat->format('Y-m-d')}' tidak cocok dengan urutan kronologis tanggal (Safe Backdate Violation).");
            }
        }

        // Generate reference number using LetterFormat
        $format = $this->letterFormats->get($row['tipe']);
        $formatString = $format ? $format->format_string : '[NO_URUT]/[KODE_UNIT]/STAI-JIC/[BULAN_ROMAWI]/[TAHUN]';
        $romans = ['01'=>'I','02'=>'II','03'=>'III','04'=>'IV','05'=>'V','06'=>'VI','07'=>'VII','08'=>'VIII','09'=>'IX','10'=>'X','11'=>'XI','12'=>'XII'];
        $monthStr = $tanggalSurat->format('m');
        $romanMonth = $romans[$monthStr] ?? $monthStr;

        $referenceNumber = str_replace(
            ['[NO_URUT]', '[KODE_UNIT]', '[BULAN_ROMAWI]', '[TAHUN]'],
            [str_pad($sequenceNumber, 3, '0', STR_PAD_LEFT), $docType->code, $romanMonth, $year],
            $formatString
        );

        // Verify reference number uniqueness in-memory
        if (isset($this->existingReferenceNumbers[$referenceNumber])) {
            throw new \Exception("Nomor referensi '{$referenceNumber}' sudah digunakan di database.");
        }

        // Register reference number in-memory cache to prevent collision with subsequent rows in same sheet
        $this->existingReferenceNumbers[$referenceNumber] = true;

        // Register in-memory sequence for backdate checks
        $uyKey = "{$unitId}_{$year}";
        $this->unitYearMails[$uyKey][] = [
            'sequence_number' => $sequenceNumber,
            'tanggal_surat' => $tanggalSurat->format('Y-m-d')
        ];
        usort($this->unitYearMails[$uyKey], function($a, $b) {
            return $a['sequence_number'] <=> $b['sequence_number'];
        });

        $receivedDate = null;
        if (!empty($row['tanggal_diterima'])) {
            try {
                $receivedDate = Carbon::parse(trim($row['tanggal_diterima']));
            } catch (\Exception $e) {
                // Keep it null or throw error
            }
        }

        return new Mail([
            'reference_number'    => $referenceNumber,
            'type'                => strtolower(trim($row['tipe'] ?? 'incoming')),
            'document_type_id'    => $docType->id,
            'unit_id'             => $unitId,
            'sequence_number'     => $sequenceNumber,
            'sender_unit'         => $row['unit_pengirim'] ?? null,
            'jenjang'             => $row['jenjang'] ?? null,
            'subject'             => $row['subjek'] ?? '',
            'body'                => $row['isi'] ?? '',
            'sender_name'         => $row['nama_pengirim'] ?? '',
            'sender_organization' => $row['organisasi_pengirim'] ?? null,
            'sender_email'        => $row['email_pengirim'] ?? null,
            'recipient_name'      => $row['nama_penerima'] ?? '',
            'recipient_department'=> $row['departemen_penerima'] ?? null,
            'recipient_email'     => $row['email_penerima'] ?? null,
            'tanggal_surat'       => $tanggalSurat,
            'received_date'       => $receivedDate,
            'priority'            => strtolower(trim($row['prioritas'] ?? 'normal')),
            'classification'      => strtolower(trim($row['klasifikasi'] ?? 'open')),
            'status'              => 'pending',
            'is_backdated'        => $isBackdated,
            'created_by'          => Auth::id(),
            'notes'               => $row['catatan'] ?? null,
        ]);
    }

    /**
     * Define validation rules for rows.
     */
    public function rules(): array
    {
        return [
            'tipe'             => 'required|in:incoming,outgoing,internal',
            'subjek'           => 'required|string|max:255',
            'isi'              => 'required|string',
            'nama_pengirim'    => 'required|string|max:150',
            'nama_penerima'    => 'required|string|max:150',
            'tanggal_surat'    => 'required',
            'prioritas'        => 'required|in:normal,urgent,very_urgent',
            'klasifikasi'      => 'required|in:open,confidential,secret',
            'kode_surat'       => 'required|string|exists:document_types,code',
            'nomor_urut'       => 'nullable|integer|min:1',
        ];
    }

    /**
     * Validate sequence order for backdating in memory.
     */
    private function validateBackdateSequenceInMemory(int $unitId, Carbon $targetDate, int $seqNumber): bool
    {
        $year = $targetDate->year;
        $uyKey = "{$unitId}_{$year}";

        // Initialize cache for unit+year if not exists
        if (!isset($this->unitYearMails[$uyKey])) {
            $this->unitYearMails[$uyKey] = Mail::where('unit_id', $unitId)
                ->whereYear('tanggal_surat', $year)
                ->orderBy('sequence_number')
                ->get(['sequence_number', 'tanggal_surat'])
                ->map(function ($m) {
                    return [
                        'sequence_number' => intval($m->sequence_number),
                        'tanggal_surat' => $m->tanggal_surat->format('Y-m-d')
                    ];
                })
                ->toArray();
        }

        $mails = $this->unitYearMails[$uyKey];
        $prevMail = null;
        $nextMail = null;

        foreach ($mails as $m) {
            $s = intval($m['sequence_number']);
            if ($s < $seqNumber) {
                $prevMail = $m; // keeps highest one < $seqNumber because array is sorted
            }
            if ($s > $seqNumber) {
                $nextMail = $m;
                break; // first one > $seqNumber is the lowest
            }
        }

        if ($prevMail && $targetDate->lt(Carbon::parse($prevMail['tanggal_surat']))) {
            return false;
        }

        if ($nextMail && $targetDate->gt(Carbon::parse($nextMail['tanggal_surat']))) {
            return false;
        }

        return true;
    }

    /**
     * Deprecated database-based validation - kept for signature compatibility
     */
    private function validateBackdateSequence(int $unitId, Carbon $targetDate, int $seqNumber): bool
    {
        return $this->validateBackdateSequenceInMemory($unitId, $targetDate, $seqNumber);
    }

    /**
     * Pre-fetch all required reference data into memory in as few queries as possible.
     * Called once before the first row is processed.
     */
    private function warmAllCaches(): void
    {
        $this->cacheWarmed = true;

        // Q1: All document types keyed by code
        $this->documentTypes = DocumentType::all()->keyBy('code');

        // Q2: All letter formats keyed by type
        $this->letterFormats = LetterFormat::all()->keyBy('type');

        // Q3: All existing reference numbers (for uniqueness check)
        $this->existingReferenceNumbers = Mail::pluck('reference_number')->flip()->toArray();

        // Q4: Existing sequences + max sequence tracker — single query, two caches.
        // Fetches all mails once and builds both existingSequences (collision check)
        // and sequenceTracker (auto-increment) in a single pass. DB-agnostic.
        $allMailSeqs = Mail::select('document_type_id', 'unit_id', 'sequence_number', 'tanggal_surat')->get();

        $this->existingSequences = [];
        foreach ($allMailSeqs as $mail) {
            $year = Carbon::parse($mail->tanggal_surat)->year;

            // Collision cache: year_docTypeId_seqNumber → true
            $colKey = "{$year}_{$mail->document_type_id}_{$mail->sequence_number}";
            $this->existingSequences[$colKey] = true;

            // Sequence tracker: unitId_year → max sequence_number
            $seqKey = "{$mail->unit_id}_{$year}";
            if (!isset($this->sequenceTracker[$seqKey]) || $mail->sequence_number > $this->sequenceTracker[$seqKey]) {
                $this->sequenceTracker[$seqKey] = (int) $mail->sequence_number;
            }
        }

        // Q5: Latest mail date per unit (for is_backdated detection)
        // Single aggregation query instead of N queries (one per unit)
        $latestPerUnit = Mail::select('unit_id', DB::raw('MAX(tanggal_surat) as latest_date'))
            ->groupBy('unit_id')
            ->get();
        foreach ($latestPerUnit as $row) {
            $this->latestMailDates[$row->unit_id] = Carbon::parse($row->latest_date);
        }
    }
}
