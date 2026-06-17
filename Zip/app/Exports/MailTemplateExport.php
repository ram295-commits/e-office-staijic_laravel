<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class MailTemplateExport implements FromArray, WithHeadings, WithTitle, ShouldAutoSize
{
    /**
     * Return headings for the export.
     */
    public function headings(): array
    {
        return [
            'Tipe',
            'Subjek',
            'Isi',
            'Nama Pengirim',
            'Organisasi Pengirim',
            'Email Pengirim',
            'Nama Penerima',
            'Departemen Penerima',
            'Email Penerima',
            'Tanggal Surat',
            'Tanggal Diterima',
            'Prioritas',
            'Klasifikasi',
            'Kode Surat',
            'Nomor Urut',
            'Unit Pengirim',
            'Jenjang',
            'Catatan'
        ];
    }

    /**
     * Return default sample data or empty array.
     */
    public function array(): array
    {
        return [
            [
                'incoming',
                'Undangan Workshop Peningkatan Mutu Akademik',
                'Sehubungan dengan diadakannya workshop penjaminan mutu, kami mengundang...',
                'Lembaga Penjaminan Mutu',
                'LPM Sekolah Tinggi Agama Islam Jajar Islamic Center Surakarta',
                'lpm@staijic.ac.id',
                'Seluruh Dosen',
                'Bagian Akademik',
                'akademik@staijic.ac.id',
                '2026-05-20',
                '2026-05-21',
                'normal',
                'open',
                'ED', // Kode Surat maps to DocumentType
                '1', // Nomor Urut, optional
                'LPM',
                'S1',
                'Harap dihadiri tepat waktu.'
            ]
        ];
    }

    /**
     * Title of the sheet.
     */
    public function title(): string
    {
        return 'Template Import Surat';
    }
}
