<?php

namespace Tests\Feature;

use App\Models\DocumentType;
use App\Models\LetterFormat;
use App\Models\Mail;
use App\Models\NumberReservation;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ArchiveWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private $staffUser;
    private $admin;
    private $manager;
    private $unit;
    private $docType;
    private $format;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create Unit
        $this->unit = Unit::create([
            'name'        => 'Unit Kepegawaian',
            'slug'        => 'unit-kepegawaian',
            'code'        => 'KP',
            'description' => 'Unit Kepegawaian JIC',
        ]);

        // 2. Create Users with designated roles
        $this->staffUser = User::factory()->create([
            'role'      => 'staff',
            'is_active' => true,
        ]);
        $this->staffUser->units()->attach($this->unit->id);

        $this->admin = User::factory()->create([
            'role'      => 'admin',
            'is_active' => true,
        ]);
        $this->admin->units()->attach($this->unit->id);

        $this->manager = User::factory()->create([
            'role'      => 'manager',
            'is_active' => true,
        ]);
        $this->manager->units()->attach($this->unit->id);

        // 3. Create Document Type
        $this->docType = DocumentType::create([
            'unit_id'     => $this->unit->id,
            'code'        => 'KP',
            'name'        => 'Surat Keputusan Kepegawaian',
            'description' => 'SK Kepegawaian',
        ]);

        // 4. Create or fetch Letter Format
        $this->format = LetterFormat::firstOrCreate(
            ['type' => 'outgoing'],
            ['format_string' => '[NO_URUT]/[KODE_UNIT]/[BULAN_ROMAWI]/[TAHUN]']
        );

        // 5. Setup Chronological boundaries:
        // Mail A (Sequence 1, Date 2026-05-01)
        Mail::create([
            'reference_number' => '001/KP/V/2026',
            'type'             => 'outgoing',
            'document_type_id' => $this->docType->id,
            'unit_id'          => $this->unit->id,
            'sequence_number'  => 1,
            'sender_name'      => 'Boundary Sender A',
            'recipient_name'   => 'Boundary Recipient A',
            'subject'          => 'Boundary Mail A',
            'body'             => 'Boundary Body A',
            'tanggal_surat'    => Carbon::parse('2026-05-01'),
            'status'           => 'completed',
            'created_by'       => $this->admin->id,
        ]);

        // Mail C (Sequence 3, Date 2026-05-10)
        Mail::create([
            'reference_number' => '003/KP/V/2026',
            'type'             => 'outgoing',
            'document_type_id' => $this->docType->id,
            'unit_id'          => $this->unit->id,
            'sequence_number'  => 3,
            'sender_name'      => 'Boundary Sender C',
            'recipient_name'   => 'Boundary Recipient C',
            'subject'          => 'Boundary Mail C',
            'body'             => 'Boundary Body C',
            'tanggal_surat'    => Carbon::parse('2026-05-10'),
            'status'           => 'completed',
            'created_by'       => $this->admin->id,
        ]);
    }

    /**
     * E2E Workflow Test: Lifecycle of a backdated archived mail.
     */
    public function test_full_archive_lifecycle_e2e()
    {
        // ---------------------------------------------------------------------
        // STEP 1: Number Reservation Request (Staff)
        // ---------------------------------------------------------------------
        $this->actingAs($this->staffUser);

        $response = $this->post(route('reservations.store'), [
            'letter_format_id' => $this->format->id,
            'document_type_id' => $this->docType->id,
            'quantity'         => 1,
            'backdate_target'  => '2026-05-05', // between Mail A (May 1) and Mail C (May 10)
            'reason'           => 'Urgent backdate for UAT testing',
        ]);

        $response->assertStatus(302); // redirect back/success
        
        $reservation = NumberReservation::where('requested_by', $this->staffUser->id)
            ->where('status', 'pending')
            ->firstOrFail();

        $this->assertEquals('2026-05-05', $reservation->backdate_target->format('Y-m-d'));
        $this->assertEquals(1, $reservation->quantity);

        // ---------------------------------------------------------------------
        // STEP 2: Approval (Admin)
        // ---------------------------------------------------------------------
        $this->actingAs($this->admin);

        $response = $this->post(route('reservations.approve', $reservation->id));
        $response->assertStatus(302);

        $reservation->refresh();
        $this->assertEquals('approved', $reservation->status);
        $this->assertEquals($this->admin->id, $reservation->approved_by);
        $this->assertCount(1, $reservation->reserved_slots);

        $slot = $reservation->reserved_slots[0];
        $this->assertEquals(2, $slot['sequence_number']); // fills vacant sequence 2
        $this->assertEquals('2026-05-05', $slot['date']);

        $mail = Mail::where('reservation_slot_id', $reservation->id)->firstOrFail();
        $this->assertEquals('draft', $mail->status);
        $this->assertTrue($mail->date_locked);
        $this->assertEquals(2, $mail->sequence_number);
        $this->assertEquals('2026-05-05', $mail->tanggal_surat->format('Y-m-d'));

        // ---------------------------------------------------------------------
        // STEP 3: Creation / Filling Slot (Staff)
        // ---------------------------------------------------------------------
        $this->actingAs($this->staffUser);

        $response = $this->put(route('reservations.fill-slot', ['reservation' => $reservation->id, 'slotIndex' => 0]), [
            'subject'              => 'Surat Keputusan Kepangkatan UAT',
            'body'                 => 'Rincian detail kenaikan pangkat staf unit JIC.',
            'sender_name'          => 'Kemendikbud Ristek',
            'sender_organization'  => 'Dirjen Dikti',
            'sender_email'         => 'dikti@kemendikbud.go.id',
            'recipient_name'       => 'Drs. H. M. Yusuf, M.Pd.',
            'recipient_department' => 'Kepegawaian',
            'recipient_email'      => 'yusuf@staijic.ac.id',
            'priority'             => 'normal',
            'classification'       => 'open',
            'notes'                => 'Catatan pengisian slot UAT',
        ]);

        $response->assertStatus(302); // Redirects to index

        $mail->refresh();
        $this->assertEquals('pending', $mail->status);
        $this->assertEquals('Surat Keputusan Kepangkatan UAT', $mail->subject);
        $this->assertEquals('Rincian detail kenaikan pangkat staf unit JIC.', $mail->body);
        $this->assertEquals('Kemendikbud Ristek', $mail->sender_name);

        // ---------------------------------------------------------------------
        // STEP 4: Processing / Status Transitions (Manager)
        // ---------------------------------------------------------------------
        $this->actingAs($this->manager);

        // Transition 1: pending -> in_progress
        $response = $this->patch(route('mails.status', $mail->id), ['status' => 'in_progress']);
        $response->assertStatus(302);
        $mail->refresh();
        $this->assertEquals('in_progress', $mail->status);

        // Transition 2: in_progress -> completed
        $response = $this->patch(route('mails.status', $mail->id), ['status' => 'completed']);
        $response->assertStatus(302);
        $mail->refresh();
        $this->assertEquals('completed', $mail->status);

        // Transition 3: completed -> archived
        $response = $this->patch(route('mails.status', $mail->id), ['status' => 'archived']);
        $response->assertStatus(302);
        $mail->refresh();
        $this->assertEquals('archived', $mail->status);

        // ---------------------------------------------------------------------
        // STEP 5: Archive Listing & Verification (Staff)
        // ---------------------------------------------------------------------
        $this->actingAs($this->staffUser);

        $response = $this->get(route('mails.archive.index'));
        $response->assertStatus(200);
        $response->assertSee($mail->reference_number);
        $response->assertSee($mail->subject);

        // ---------------------------------------------------------------------
        // STEP 6: Export & Select Query Verification (Manager)
        // ---------------------------------------------------------------------
        $this->actingAs($this->manager);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $response = $this->get(route('mails.archive.export'));
        $response->assertStatus(200);

        // Capture streamed response content
        ob_start();
        $response->sendContent();
        $csvContent = ob_get_clean();

        // Assert CSV matches data
        $this->assertStringContainsString($mail->reference_number, $csvContent);
        $this->assertStringContainsString($mail->subject, $csvContent);
        $this->assertStringContainsString('archived', $csvContent);

        // Verify that the query on the mails table did NOT load the 'body' column
        $queries = DB::getQueryLog();
        $this->assertNotEmpty($queries);

        $mailQueries = array_filter($queries, function ($q) {
            return str_contains(strtolower($q['query']), 'select') && str_contains(strtolower($q['query']), 'mails');
        });

        $this->assertNotEmpty($mailQueries);

        foreach ($mailQueries as $q) {
            $sql = strtolower($q['query']);
            
            // Check that selecting '*' was NOT used
            $this->assertStringNotContainsString('select *', $sql);
            
            // Check that the body column was NOT loaded
            $this->assertStringNotContainsString('"body"', $sql);
            $this->assertStringNotContainsString('`body`', $sql);
            $this->assertStringNotContainsString(' body', $sql);
        }

        DB::disableQueryLog();
    }
}
