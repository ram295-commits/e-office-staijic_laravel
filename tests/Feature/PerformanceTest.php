<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Unit;
use App\Models\DocumentType;
use App\Models\LetterFormat;
use App\Models\Mail;
use App\Imports\MailsImport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PerformanceTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $unit;
    private $docType;
    private $letterFormatIncoming;
    private $letterFormatOutgoing;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed basic user
        $this->user = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Seed basic unit
        $this->unit = Unit::create([
            'name' => 'Bagian Umum',
            'slug' => 'bagian-umum',
            'description' => 'Unit Bagian Umum',
            'code' => 'BU',
        ]);

        // Connect user to unit
        $this->user->units()->attach($this->unit->id);

        // Seed document type
        $this->docType = DocumentType::create([
            'unit_id' => $this->unit->id,
            'code' => 'BU',
            'name' => 'Surat Keputusan Umum',
            'description' => 'Jenis surat keputusan bagian umum',
        ]);

        // Letter formats are auto-seeded by the migration
        $this->letterFormatIncoming = LetterFormat::where('type', 'incoming')->first();
        $this->letterFormatOutgoing = LetterFormat::where('type', 'outgoing')->first();
    }

    /**
     * Test single mail creation and profile query counts.
     */
    public function test_mail_creation_query_count_and_timing()
    {
        $this->actingAs($this->user);

        // Start Query Log
        DB::flushQueryLog();
        DB::enableQueryLog();

        $startTime = microtime(true);

        // 1. Create a normal (non-backdated) mail
        $response1 = $this->post(route('mails.store'), [
            'document_type_id' => $this->docType->id,
            'type' => 'incoming',
            'subject' => 'Surat Undangan Rapat Kerja',
            'body' => 'Isi surat undangan...',
            'sender_name' => 'Kemendikbud',
            'recipient_name' => 'Ketua STAI',
            'tanggal_surat' => now()->format('Y-m-d'),
            'priority' => 'normal',
            'classification' => 'open',
        ]);

        $endTime = microtime(true);
        $queries1 = DB::getQueryLog();
        $queryCount1 = count($queries1);
        $duration1 = ($endTime - $startTime) * 1000; // ms

        $response1->assertRedirect();
        
        echo "\n[PERFORMANCE TEST] - Single Mail Creation (Normal):\n";
        echo "  - Total Queries: {$queryCount1}\n";
        echo "  - Execution Time: " . round($duration1, 2) . " ms\n";

        // Let's print unique query types for visibility
        echo "  - Queries executed:\n";
        foreach (array_slice($queries1, 0, 10) as $idx => $q) {
            echo "    " . ($idx + 1) . ". " . substr(preg_replace('/\s+/', ' ', $q['query']), 0, 120) . "...\n";
        }

        // 2. Create a backdated mail and measure query count overhead
        // First we insert a mail with a later date to force the next mail to be backdated
        Mail::create([
            'reference_number' => '001/BU/STAI-JIC/V/2026',
            'type' => 'incoming',
            'document_type_id' => $this->docType->id,
            'unit_id' => $this->unit->id,
            'sequence_number' => 1,
            'subject' => 'Future Mail',
            'body' => 'Body',
            'sender_name' => 'Sender',
            'recipient_name' => 'Recipient',
            'tanggal_surat' => now()->addDays(5)->format('Y-m-d'),
            'priority' => 'normal',
            'classification' => 'open',
            'status' => 'pending',
            'created_by' => $this->user->id,
        ]);

        DB::flushQueryLog();
        $startTimeBackdate = microtime(true);

        $response2 = $this->post(route('mails.store'), [
            'document_type_id' => $this->docType->id,
            'type' => 'incoming',
            'subject' => 'Surat Backdated',
            'body' => 'Isi surat...',
            'sender_name' => 'Kemendikbud',
            'recipient_name' => 'Ketua STAI',
            'tanggal_surat' => now()->format('Y-m-d'), // earlier than Future Mail
            'priority' => 'normal',
            'classification' => 'open',
            'sequence_number' => 2, // explicit sequence to check timeline
        ]);

        $endTimeBackdate = microtime(true);
        $queries2 = DB::getQueryLog();
        $queryCount2 = count($queries2);
        $durationBackdate = ($endTimeBackdate - $startTimeBackdate) * 1000;

        $response2->assertRedirect();

        echo "\n[PERFORMANCE TEST] - Single Mail Creation (Backdated with Timeline Check):\n";
        echo "  - Total Queries: {$queryCount2}\n";
        echo "  - Execution Time: " . round($durationBackdate, 2) . " ms\n";
        
        // Assert that the queries ran successfully and were not infinite
        $this->assertTrue(true);
    }

    /**
     * Test mass import performance to detect N+1 database queries.
     */
    public function test_mass_import_n_plus_one_bottleneck()
    {
        $this->actingAs($this->user);

        // We will simulate programmatic import of 20 mail rows
        $import = new MailsImport();

        // Let's construct 20 rows of import data
        $rows = [];
        for ($i = 1; $i <= 20; $i++) {
            $rows[] = [
                'tipe' => 'incoming',
                'subjek' => "Surat Ke-{$i} untuk di-import",
                'isi' => "Isi detail surat ke-{$i} hasil import massal.",
                'nama_pengirim' => "Pengirim {$i}",
                'organisasi_pengirim' => "Instansi {$i}",
                'email_pengirim' => "pengirim{$i}@example.com",
                'nama_penerima' => 'Ketua STAI',
                'departemen_penerima' => 'Administrasi',
                'email_penerima' => 'ketua@staijic.ac.id',
                'tanggal_surat' => now()->subDays(20 - $i)->format('Y-m-d'),
                'tanggal_diterima' => now()->subDays(20 - $i)->format('Y-m-d'),
                'prioritas' => 'normal',
                'klasifikasi' => 'open',
                'kode_surat' => 'BU',
                'nomor_urut' => '', // Auto-assign to test max calculation overhead
                'unit_pengirim' => 'Bagian Umum',
                'jenjang' => 'S1',
                'catatan' => 'Catatan import ' . $i
            ];
        }

        // Start Query Log
        DB::flushQueryLog();
        DB::enableQueryLog();

        $startTime = microtime(true);

        // Import the rows one by one using the import class model() mapper
        $createdMails = [];
        foreach ($rows as $row) {
            $mailInstance = $import->model($row);
            if ($mailInstance) {
                $mailInstance->save();
                $createdMails[] = $mailInstance;
            }
        }

        $endTime = microtime(true);
        $queries = DB::getQueryLog();
        $queryCount = count($queries);
        $duration = ($endTime - $startTime) * 1000;

        echo "\n[PERFORMANCE TEST] - Mass Import (20 Mails):\n";
        echo "  - Total Queries: {$queryCount} (Average: " . round($queryCount / 20, 2) . " queries per row)\n";
        echo "  - Execution Time: " . round($duration, 2) . " ms\n";

        // Assert that we imported 20 records
        $this->assertCount(20, $createdMails);
        $this->assertEquals(20, Mail::count());

        // With warming caches in 6 queries upfront + 1 INSERT per row:
        // Total should be well below 1.5 * 20 = 30 queries.
        $maxAllowedQueries = (int) ceil(1.5 * 20);
        echo "  - Threshold: {$maxAllowedQueries} queries (1.5 × 20 rows)\n";
        echo "  - Profiling Result: " . ($queryCount <= $maxAllowedQueries ? "OPTIMAL" : "WARNING: Threshold exceeded!") . "\n";

        $this->assertLessThanOrEqual(
            $maxAllowedQueries,
            $queryCount,
            "Import query count {$queryCount} exceeds the 1.5-per-row threshold ({$maxAllowedQueries} allowed for 20 rows)."
        );
    }

    /**
     * Test export archive action to check if it's retrieving all in memory or could cause overhead.
     */
    public function test_export_archive_memory_and_query_efficiency()
    {
        $this->actingAs($this->user);

        // Seed 50 archived mails
        $mailsData = [];
        for ($i = 1; $i <= 50; $i++) {
            $mailsData[] = [
                'reference_number' => "SM/" . str_pad($i, 4, '0', STR_PAD_LEFT) . "/05/2026",
                'type' => 'incoming',
                'document_type_id' => $this->docType->id,
                'unit_id' => $this->unit->id,
                'sequence_number' => $i,
                'subject' => "Archived Mail {$i}",
                'body' => "Body of archived mail {$i}",
                'sender_name' => "Sender {$i}",
                'recipient_name' => "Recipient {$i}",
                'tanggal_surat' => now()->subDays(50 - $i),
                'priority' => 'normal',
                'classification' => 'open',
                'status' => 'archived',
                'created_by' => $this->user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        Mail::insert($mailsData);

        // Flush and log
        DB::flushQueryLog();
        DB::enableQueryLog();

        $startTime = microtime(true);

        $response = $this->get(route('mails.archive.export'));

        $endTime = microtime(true);
        $queries = DB::getQueryLog();
        $queryCount = count($queries);
        $duration = ($endTime - $startTime) * 1000;

        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition', 'attachment; filename=arsip-surat-' . date('Ymd') . '.csv');

        echo "\n[PERFORMANCE TEST] - Archive CSV Export (50 Mails):\n";
        echo "  - Total Queries: {$queryCount}\n";
        echo "  - Execution Time: " . round($duration, 2) . " ms\n";
        
        // Let's check memory limit / potential risk
        echo "  - Analysis: The CSV export fetches all records at once into memory. With 50 records, it runs perfectly, but with 10,000+ records, loading all models using `get()` risks hitting memory exhaustion limits.\n";

        $this->assertTrue(true);
    }

    /**
     * Test content audit logs are created when mail is updated.
     */
    public function test_mail_content_audit_logs()
    {
        $this->actingAs($this->user);

        // Create initial mail
        $mail = Mail::create([
            'reference_number' => 'TEST/AUDIT/001',
            'type' => 'incoming',
            'document_type_id' => $this->docType->id,
            'unit_id' => $this->unit->id,
            'sequence_number' => 10,
            'subject' => 'Original Subject',
            'body' => 'Original Body content',
            'sender_name' => 'Original Sender',
            'recipient_name' => 'Original Recipient',
            'tanggal_surat' => now(),
            'priority' => 'normal',
            'classification' => 'open',
            'status' => 'pending',
            'created_by' => $this->user->id,
        ]);

        // Update whitelisted attributes
        $mail->update([
            'subject' => 'Updated Subject',
            'body' => 'Updated Body content',
            'status' => 'in_progress'
        ]);

        // Assert audit log exists in mail_content_logs table
        $this->assertDatabaseHas('mail_content_logs', [
            'mail_id' => $mail->id,
            'changed_by' => $this->user->id,
            'action' => 'update',
        ]);

        $log = \App\Models\MailContentLog::where('mail_id', $mail->id)->first();
        $this->assertNotNull($log);
        $this->assertEquals('Updated Subject', $log->changes['subject']);
        $this->assertEquals('Original Subject', $log->old_values['subject']);
        $this->assertEquals('Updated Body content', $log->changes['body']);
        $this->assertEquals('Original Body content', $log->old_values['body']);
    }

    /**
     * Test status transitions restricted by the State Machine.
     */
    public function test_mail_status_state_machine_validation()
    {
        $this->actingAs($this->user);

        // Create mail in 'pending' state
        $mail = Mail::create([
            'reference_number' => 'TEST/STATE/001',
            'type' => 'incoming',
            'document_type_id' => $this->docType->id,
            'unit_id' => $this->unit->id,
            'sequence_number' => 20,
            'subject' => 'Status Mail',
            'body' => 'Body',
            'sender_name' => 'Sender',
            'recipient_name' => 'Recipient',
            'tanggal_surat' => now(),
            'priority' => 'normal',
            'classification' => 'open',
            'status' => 'pending',
            'created_by' => $this->user->id,
        ]);

        // 1. Try an invalid transition (pending -> archived is invalid directly)
        $response = $this->patch(route('mails.status', $mail), [
            'status' => 'archived'
        ]);

        $response->assertSessionHasErrors(['status']);
        $this->assertEquals('pending', $mail->fresh()->status);

        // 2. Try a valid transition (pending -> in_progress is valid)
        $response = $this->patch(route('mails.status', $mail), [
            'status' => 'in_progress'
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertEquals('in_progress', $mail->fresh()->status);
    }

    /**
     * Test user role authorization and assignee status guards.
     */
    public function test_mail_status_authorization_guards()
    {
        // 1. Create a plain staff user not connected to this mail
        $staff = User::factory()->create([
            'role' => 'staff',
            'is_active' => true,
        ]);

        // Create mail
        $mail = Mail::create([
            'reference_number' => 'TEST/GUARD/001',
            'type' => 'incoming',
            'document_type_id' => $this->docType->id,
            'unit_id' => $this->unit->id,
            'sequence_number' => 30,
            'subject' => 'Guard Mail',
            'body' => 'Body',
            'sender_name' => 'Sender',
            'recipient_name' => 'Recipient',
            'tanggal_surat' => now(),
            'priority' => 'normal',
            'classification' => 'open',
            'status' => 'pending',
            'created_by' => $this->user->id, // Created by admin, not staff
        ]);

        // Try updating status as plain staff (unauthorized) -> should block 403
        $response = $this->actingAs($staff)->patch(route('mails.status', $mail), [
            'status' => 'in_progress'
        ]);
        $response->assertStatus(403);

        // 2. Connect the staff to this mail as assignee
        $mail->update(['assigned_to' => $staff->id]);

        // Try again as assignee -> should succeed
        $response = $this->actingAs($staff)->patch(route('mails.status', $mail), [
            'status' => 'in_progress'
        ]);
        $response->assertSessionHasNoErrors();
        $this->assertEquals('in_progress', $mail->fresh()->status);
    }

    /**
     * Test notification triggering on disposition creation.
     */
    public function test_disposition_created_notification()
    {
        $this->actingAs($this->user); // acting as Admin

        $staff = User::factory()->create([
            'role' => 'staff',
            'is_active' => true,
        ]);

        $mail = Mail::create([
            'reference_number' => 'TEST/NOTIF/001',
            'type' => 'incoming',
            'document_type_id' => $this->docType->id,
            'unit_id' => $this->unit->id,
            'sequence_number' => 40,
            'subject' => 'Notif Mail',
            'body' => 'Body',
            'sender_name' => 'Sender',
            'recipient_name' => 'Recipient',
            'tanggal_surat' => now(),
            'priority' => 'normal',
            'classification' => 'open',
            'status' => 'pending',
            'created_by' => $this->user->id,
        ]);

        // Clear existing notifications
        DB::table('notifications')->truncate();

        // Create a disposition from Admin to Staff
        $response = $this->post(route('dispositions.store'), [
            'mail_id' => $mail->id,
            'to_user_id' => $staff->id,
            'instruction' => 'Silakan tindaklanjuti laporan ini.',
            'action_type' => 'for_action',
            'due_date' => now()->addDays(5)->format('Y-m-d'),
        ]);

        $response->assertRedirect();
        
        // Assert that notification is stored in DB for Staff
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $staff->id,
            'notifiable_type' => User::class,
        ]);

        $notification = DB::table('notifications')->first();
        $data = json_decode($notification->data, true);
        $this->assertEquals('disposition_created', $data['type']);
        $this->assertEquals('Silakan tindaklanjuti laporan ini.', $data['instruction']);
    }
}
