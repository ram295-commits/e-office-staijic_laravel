<?php

namespace App\Imports;

use App\Models\DocumentType;
use App\Models\Unit;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Validators\Failure;
use Throwable;

class DocumentTypeImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    use SkipsErrors, SkipsFailures;

    /** Count of rows that were skipped due to duplicate code */
    public int $skipped = 0;

    /** Count of rows successfully imported */
    public int $imported = 0;

    /**
     * Map each row to a DocumentType model.
     * Returns null to skip (on duplicate code).
     */
    public function model(array $row): ?DocumentType
    {
        $code = strtoupper(trim($row['kode'] ?? ''));
        $name = trim($row['nama_jenis_surat'] ?? '');

        if (empty($code) || empty($name)) {
            $this->skipped++;
            return null;
        }

        // Skip if the code already exists
        if (DocumentType::where('code', $code)->exists()) {
            $this->skipped++;
            return null;
        }

        // Resolve unit by name (case-insensitive) — null if not found
        $unitName = trim($row['unit_terkait'] ?? '');
        $unit = Unit::whereRaw('LOWER(name) = ?', [strtolower($unitName)])->first();

        $this->imported++;

        return new DocumentType([
            'code'        => $code,
            'name'        => $name,
            'unit_id'     => $unit?->id,
            'description' => trim($row['deskripsi'] ?? ''),
        ]);
    }

    /**
     * Validation rules applied per-row before model() is called.
     */
    public function rules(): array
    {
        return [
            'nama_jenis_surat' => ['required', 'string', 'max:255'],
            'kode'             => ['required', 'string', 'max:50'],
        ];
    }

    /**
     * Custom validation messages.
     */
    public function customValidationMessages(): array
    {
        return [
            'nama_jenis_surat.required' => 'Kolom "Nama Jenis Surat" wajib diisi.',
            'kode.required'             => 'Kolom "Kode" wajib diisi.',
        ];
    }
}
