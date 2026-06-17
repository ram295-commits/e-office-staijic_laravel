<?php

namespace Database\Seeders;

use App\Models\Disposition;
use App\Models\DocumentType;
use App\Models\LetterFormat;
use App\Models\Mail;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    private const ROMAN_MONTHS = [
        '01' => 'I',  '02' => 'II',  '03' => 'III', '04' => 'IV',
        '05' => 'V',  '06' => 'VI',  '07' => 'VII', '08' => 'VIII',
        '09' => 'IX', '10' => 'X',   '11' => 'XI',  '12' => 'XII',
    ];

    public function run(): void
    {
        $this->call(DocumentTypeSeeder::class);

        $arsipUnit = Unit::where('slug', 'internal-arsip')->firstOrFail();
        $lembagaUnit = Unit::where('slug', 'lembaga')->firstOrFail();

        $incomingDocType = DocumentType::where('code', 'BLS')->where('unit_id', $arsipUnit->id)->firstOrFail();
        $outgoingDocType = DocumentType::where('code', 'SK')->where('unit_id', $lembagaUnit->id)->firstOrFail();
        $internalDocType = DocumentType::where('code', 'MEMO')->where('unit_id', $arsipUnit->id)->firstOrFail();

        $admin = User::create([
            'name'       => 'Administrator',
            'nip'        => '198001012000011001',
            'email'      => 'admin@eoffice.local',
            'password'   => Hash::make('admin123'),
            'department' => 'Tata Usaha',
            'position'   => 'Administrator Sistem',
            'role'       => 'admin',
            'is_active'  => true,
        ]);

        $manager = User::create([
            'name'       => 'Budi Santoso',
            'nip'        => '197505152003121005',
            'email'      => 'manager@eoffice.local',
            'password'   => Hash::make('manager123'),
            'department' => 'Kepala Bagian Umum',
            'position'   => 'Kepala Bagian',
            'role'       => 'manager',
            'is_active'  => true,
        ]);

        $staff1 = User::create([
            'name'       => 'Siti Rahayu',
            'nip'        => '199001012018012001',
            'email'      => 'siti@eoffice.local',
            'password'   => Hash::make('staff123'),
            'department' => 'Bagian Umum',
            'position'   => 'Staf Administrasi',
            'role'       => 'staff',
            'is_active'  => true,
        ]);

        $staff2 = User::create([
            'name'       => 'Ahmad Fauzi',
            'nip'        => '199502222020011002',
            'email'      => 'ahmad@eoffice.local',
            'password'   => Hash::make('staff123'),
            'department' => 'Bagian Keuangan',
            'position'   => 'Staf Keuangan',
            'role'       => 'staff',
            'is_active'  => true,
        ]);

        $admin->units()->attach([$arsipUnit->id, $lembagaUnit->id]);
        $manager->units()->attach([$arsipUnit->id, $lembagaUnit->id]);
        $staff1->units()->attach($arsipUnit->id);
        $staff2->units()->attach($lembagaUnit->id);

        $mail1Date = now()->subDays(5);
        $mail2Date = now()->subDays(2);
        $mail3Date = now()->subDays(3);
        $mail4Date = now()->subDays(1);

        $mail1 = Mail::create([
            'reference_number'    => $this->buildReferenceNumber('incoming', 1, $arsipUnit->code, $mail1Date),
            'type'                => 'incoming',
            'document_type_id'    => $incomingDocType->id,
            'unit_id'             => $arsipUnit->id,
            'sequence_number'     => 1,
            'subject'             => 'Undangan Rapat Koordinasi Nasional Bidang Administrasi',
            'body'                => "Bersama ini kami mengundang Bapak/Ibu untuk menghadiri Rapat Koordinasi Nasional Bidang Administrasi yang akan dilaksanakan pada:\n\nHari   : Selasa, 20 Mei 2026\nWaktu  : 09.00 WIB - Selesai\nTempat : Aula Kantor Pusat\n\nDemikian undangan ini kami sampaikan, atas kehadirannya kami ucapkan terima kasih.",
            'sender_name'         => 'Kementerian Dalam Negeri',
            'sender_organization' => 'Direktorat Jenderal Bina Administrasi Kewilayahan',
            'sender_email'        => 'bak@kemendagri.go.id',
            'recipient_name'      => 'Kepala Dinas',
            'tanggal_surat'       => $mail1Date,
            'received_date'       => now()->subDays(4),
            'priority'            => 'urgent',
            'classification'      => 'open',
            'status'              => 'pending',
            'created_by'          => $admin->id,
            'assigned_to'         => $manager->id,
        ]);

        $mail2 = Mail::create([
            'reference_number'    => $this->buildReferenceNumber('incoming', 2, $arsipUnit->code, $mail2Date),
            'type'                => 'incoming',
            'document_type_id'    => $incomingDocType->id,
            'unit_id'             => $arsipUnit->id,
            'sequence_number'     => 2,
            'subject'             => 'Permohonan Data Laporan Kinerja Triwulan I Tahun 2026',
            'body'                => 'Sehubungan dengan penyusunan laporan kinerja instansi pemerintah, mohon kiranya dapat menyampaikan data laporan kinerja triwulan I tahun 2026 selambat-lambatnya pada tanggal 30 Mei 2026.',
            'sender_name'         => 'Badan Pengawasan Keuangan dan Pembangunan',
            'sender_organization' => 'BPKP',
            'recipient_name'      => 'Kepala Dinas',
            'tanggal_surat'       => $mail2Date,
            'received_date'       => now()->subDays(1),
            'priority'            => 'urgent',
            'classification'      => 'open',
            'status'              => 'in_progress',
            'created_by'          => $staff1->id,
            'assigned_to'         => $staff1->id,
        ]);

        $mail3 = Mail::create([
            'reference_number'    => $this->buildReferenceNumber('outgoing', 1, $lembagaUnit->code, $mail3Date),
            'type'                => 'outgoing',
            'document_type_id'    => $outgoingDocType->id,
            'unit_id'             => $lembagaUnit->id,
            'sequence_number'     => 1,
            'subject'             => 'Balasan Undangan Rapat Koordinasi Nasional',
            'body'                => 'Menanggapi undangan rapat koordinasi yang Bapak/Ibu sampaikan, dengan hormat kami menyatakan bersedia hadir dalam kegiatan tersebut.',
            'sender_name'         => 'Kepala Dinas',
            'sender_organization' => 'Dinas Administrasi',
            'recipient_name'      => 'Kementerian Dalam Negeri',
            'recipient_department'=> 'Direktorat Jenderal Bina Administrasi',
            'recipient_email'     => 'bak@kemendagri.go.id',
            'tanggal_surat'       => $mail3Date,
            'priority'            => 'normal',
            'classification'      => 'open',
            'status'              => 'completed',
            'created_by'          => $manager->id,
        ]);

        $mail4 = Mail::create([
            'reference_number'    => $this->buildReferenceNumber('internal', 3, $arsipUnit->code, $mail4Date),
            'type'                => 'internal',
            'document_type_id'    => $internalDocType->id,
            'unit_id'             => $arsipUnit->id,
            'sequence_number'     => 3,
            'subject'             => 'Pemberitahuan Jadwal Cuti Bersama Tahun 2026',
            'body'                => 'Diberitahukan kepada seluruh pegawai bahwa jadwal cuti bersama tahun 2026 telah ditetapkan. Mohon untuk memperhatikan jadwal tersebut dalam perencanaan kegiatan.',
            'sender_name'         => 'Kepala Bagian Umum',
            'sender_organization' => 'Bagian Umum',
            'recipient_name'      => 'Seluruh Pegawai',
            'recipient_department'=> 'Semua Bagian',
            'tanggal_surat'       => $mail4Date,
            'priority'            => 'normal',
            'classification'      => 'open',
            'status'              => 'completed',
            'created_by'          => $manager->id,
        ]);

        Disposition::create([
            'mail_id'      => $mail1->id,
            'from_user_id' => $manager->id,
            'to_user_id'   => $staff1->id,
            'instruction'  => 'Mohon segera direspons dan siapkan bahan presentasi untuk rapat koordinasi ini. Koordinasikan dengan bagian terkait.',
            'action_type'  => 'for_action',
            'due_date'     => now()->addDays(7),
            'status'       => 'pending',
        ]);

        Disposition::create([
            'mail_id'      => $mail2->id,
            'from_user_id' => $manager->id,
            'to_user_id'   => $staff2->id,
            'instruction'  => 'Kompilasikan data laporan kinerja dari seluruh sub-bagian dan kirimkan ke BPKP sesuai batas waktu.',
            'action_type'  => 'for_action',
            'due_date'     => now()->addDays(3),
            'status'       => 'in_progress',
            'response_notes' => 'Sedang dalam proses pengumpulan data dari setiap sub-bagian.',
            'responded_at' => now()->subHours(2),
        ]);
    }

    private function buildReferenceNumber(string $type, int $sequence, string $unitCode, Carbon $tanggalSurat): string
    {
        $format = LetterFormat::where('type', $type)->first();
        $formatString = $format?->format_string ?? '[NO_URUT]/[KODE_UNIT]/STAI-JIC/[BULAN_ROMAWI]/[TAHUN]';
        $monthStr = $tanggalSurat->format('m');
        $romanMonth = self::ROMAN_MONTHS[$monthStr] ?? $monthStr;

        return str_replace(
            ['[NO_URUT]', '[KODE_UNIT]', '[BULAN_ROMAWI]', '[TAHUN]'],
            [str_pad((string) $sequence, 3, '0', STR_PAD_LEFT), $unitCode, $romanMonth, $tanggalSurat->year],
            $formatString
        );
    }
}
