<?php

namespace App\Exports;

use App\Models\DocumentType;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DocumentTypeExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    /**
     * Fetch all document types with their related unit.
     */
    public function collection()
    {
        return DocumentType::with('unit')
            ->orderBy('code')
            ->get()
            ->map(function ($type, $index) {
                return [
                    'no'          => $index + 1,
                    'name'        => $type->name,
                    'code'        => $type->code,
                    'unit'        => $type->unit->name ?? '—',
                    'description' => $type->description ?? '',
                ];
            });
    }

    /**
     * Column headings row.
     */
    public function headings(): array
    {
        return [
            'No',
            'Nama Jenis Surat',
            'Kode',
            'Unit Terkait',
            'Deskripsi',
        ];
    }

    /**
     * Sheet name.
     */
    public function title(): string
    {
        return 'Jenis Surat';
    }

    /**
     * Bold the header row.
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
