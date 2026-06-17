<?php

namespace Database\Seeders;

use App\Models\Unit;
use App\Models\DocumentType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DocumentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $matrix = [
            [
                'name' => 'LEMBAGA',
                'slug' => 'lembaga',
                'description' => 'Kebijakan dan dokumen tingkat institusi/pimpinan',
                'documents' => [
                    ['code' => 'SK',      'name' => 'Surat Keputusan Pimpinan',  'description' => 'Kebijakan/Aturan Kampus'],
                    ['code' => 'ED',      'name' => 'Surat Edaran',              'description' => 'Informasi massal'],
                    ['code' => 'MOU',     'name' => 'MoA / MoU',                 'description' => 'Nota kesepahaman'],
                    ['code' => 'PKS',     'name' => 'Perjanjian Kerja Sama',     'description' => 'Detail teknis operasional'],
                    ['code' => 'SAH',     'name' => 'Surat Pengesahan',          'description' => 'Pengesahan dokumen/statuta'],
                    ['code' => 'REC.GEN', 'name' => 'Surat Rekomendasi Umum',   'description' => 'Rekomendasi umum/lembaga'],
                ],
            ],
            [
                'name' => 'BIDANG 1 / AKADEMIK',
                'slug' => 'bidang-1-akademik',
                'description' => 'Urusan akademik, penelitian, dan kemahasiswaan akademis',
                'documents' => [
                    ['code' => 'SKL',     'name' => 'Surat Keterangan Lulus',       'description' => 'Pengganti ijazah sementara'],
                    ['code' => 'LIT',     'name' => 'Surat Keterangan Penelitian',  'description' => 'Izin riset/observasi'],
                    ['code' => 'REC.AKD', 'name' => 'Surat Rekomendasi Akademik',  'description' => 'Rekomendasi lanjut studi'],
                    ['code' => 'SK.MHS',  'name' => 'SK Mahasiswa',                'description' => 'Penetapan status (Drop Out/Cuti)'],
                    ['code' => 'SRT',     'name' => 'Sertifikat Akademik',         'description' => 'Sertifikat pemateri/peserta'],
                    ['code' => 'BBS.ADM', 'name' => 'Surat Bebas Administrasi',   'description' => 'Syarat wisuda/cuti'],
                ],
            ],
            [
                'name' => 'BIDANG 2 / ADM. UMUM & KEUANGAN',
                'slug' => 'bidang-2-adm-umum-keuangan',
                'description' => 'Administrasi umum, kepegawaian, dan keuangan institusi',
                'documents' => [
                    ['code' => 'ST.PGW',  'name' => 'Surat Tugas Pegawai',            'description' => 'Dinas luar/workshop/pelatihan'],
                    ['code' => 'SK.ANG',  'name' => 'SK Pengangkatan',                'description' => 'Dosen tetap/Pegawai tetap'],
                    ['code' => 'SK.MUT',  'name' => 'SK Mutasi',                      'description' => 'Pindah unit kerja'],
                    ['code' => 'SK.BRH',  'name' => 'SK Pemberhentian',               'description' => 'Resign/Pensiun'],
                    ['code' => 'CT.PGW',  'name' => 'Surat Cuti Pegawai',             'description' => 'Cuti tahunan/melahirkan'],
                    ['code' => 'KET.PGW', 'name' => 'Surat Keterangan Pegawai',       'description' => 'Keterangan kerja/penghasilan'],
                    ['code' => 'SP',      'name' => 'Surat Peringatan',               'description' => 'Teguran disiplin (SP 1-3)'],
                    ['code' => 'TAG',     'name' => 'Surat Tagihan',                  'description' => 'Tagihan SPP/UKT/Vendor'],
                    ['code' => 'PMH.DAN', 'name' => 'Permohonan Dana',               'description' => 'Pengajuan anggaran unit'],
                    ['code' => 'SPJ',     'name' => 'Surat Pertanggungjawaban',       'description' => 'Laporan penggunaan dana'],
                    ['code' => 'KWT',     'name' => 'Kwitansi',                       'description' => 'Bukti bayar internal'],
                    ['code' => 'SJN',     'name' => 'Surat Jalan',                   'description' => 'Logistik/pengiriman barang'],
                ],
            ],
            [
                'name' => 'BIDANG 3 / KEMAHASISWAAN',
                'slug' => 'bidang-3-kemahasiswaan',
                'description' => 'Urusan kemahasiswaan, organisasi, dan beasiswa',
                'documents' => [
                    ['code' => 'AKT',     'name' => 'Surat Aktif Kuliah',               'description' => 'Khusus untuk tunjangan anak/BPJS'],
                    ['code' => 'DSP',     'name' => 'Surat Dispensasi',                 'description' => 'Izin tidak kuliah karena kegiatan'],
                    ['code' => 'REC.BSW', 'name' => 'Rekomendasi Beasiswa',            'description' => 'Pengajuan beasiswa internal/eksternal'],
                    ['code' => 'KET.ORG', 'name' => 'Keterangan Aktif Organisasi',     'description' => 'Pengurus Ormawa/UKM'],
                    ['code' => 'ST.MHS',  'name' => 'Surat Tugas Mahasiswa',           'description' => 'Tugas perlombaan/delegasi'],
                    ['code' => 'KET.MHS', 'name' => 'Surat Keterangan Mahasiswa',      'description' => 'Keterangan mahasiswa aktif'],
                ],
            ],
            [
                'name' => 'PRODI (PENDIDIKAN & HUKUM)',
                'slug' => 'prodi-pendidikan-hukum',
                'description' => 'Operasional program studi Pendidikan Islam & Hukum Tata Negara',
                'documents' => [
                    ['code' => 'PMH', 'name' => 'Surat Permohonan', 'description' => 'Permohonan izin, sarpras, dll.'],
                    ['code' => 'UND', 'name' => 'Surat Undangan',   'description' => 'Rapat internal/eksternal prodi'],
                    ['code' => 'PJM', 'name' => 'Surat Peminjaman', 'description' => 'Peminjam gedung/alat'],
                    ['code' => 'BA',  'name' => 'Berita Acara',     'description' => 'Kejadian/serah terima/rapat prodi'],
                ],
            ],
            [
                'name' => 'INTERNAL / ARSIP',
                'slug' => 'internal-arsip',
                'description' => 'Komunikasi internal dan dokumen kearsipan umum',
                'documents' => [
                    ['code' => 'PNG',  'name' => 'Surat Pengantar',            'description' => 'Pengantar dokumen ke instansi lain'],
                    ['code' => 'PBT',  'name' => 'Surat Pemberitahuan',        'description' => 'Pengumuman libur/edaran umum'],
                    ['code' => 'BLS',  'name' => 'Surat Balasan',              'description' => 'Jawaban atas surat masuk'],
                    ['code' => 'KET',  'name' => 'Surat Keterangan Umum',      'description' => 'Keterangan domisili/umum'],
                    ['code' => 'PNY',  'name' => 'Surat Pernyataan',           'description' => 'Pernyataan tanggung jawab mutlak'],
                    ['code' => 'KSA',  'name' => 'Surat Kuasa',                'description' => 'Pelimpahan wewenang sementara'],
                    ['code' => 'ND',   'name' => 'Nota Dinas',                 'description' => 'Komunikasi antar pejabat internal'],
                    ['code' => 'MEMO', 'name' => 'Memo Internal / Notulensi',  'description' => 'Informasi/Instruksi singkat'],
                    ['code' => 'SPJ.PJNJ', 'name' => 'Surat Perjanjian',      'description' => 'Kontrak kerja sama pihak ketiga'],
                ],
            ],
        ];

        foreach ($matrix as $unitData) {
            $code = match ($unitData['slug']) {
                'lembaga'                    => 'LEMBAGA',
                'bidang-1-akademik'          => 'AKD',
                'bidang-2-adm-umum-keuangan'=> 'KU',
                'bidang-3-kemahasiswaan'     => 'MHS',
                'prodi-pendidikan-hukum'     => 'PRODI',
                'internal-arsip'             => 'ARSIP',
                default                      => strtoupper(str_replace('-', '_', $unitData['slug'])),
            };

            $unit = Unit::firstOrCreate(
                ['slug' => $unitData['slug']],
                [
                    'name'        => $unitData['name'],
                    'code'        => $code,
                    'description' => $unitData['description'],
                ]
            );

            if (!$unit->code) {
                $unit->update(['code' => $code]);
            }

            foreach ($unitData['documents'] as $doc) {
                DocumentType::firstOrCreate(
                    ['code' => $doc['code']],
                    [
                        'unit_id' => $unit->id,
                        'name' => $doc['name'],
                        'description' => $doc['description'],
                    ]
                );
            }
        }

        $this->command->info('✅ Seeded 6 units and 43 document types successfully.');
    }
}
