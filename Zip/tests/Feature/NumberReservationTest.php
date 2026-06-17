<?php

namespace Tests\Feature;

use App\Models\DocumentType;
use App\Models\LetterFormat;
use App\Models\Mail;
use App\Models\NumberReservation;
use App\Models\Unit;
use App\Models\User;
use App\Services\ChronologicalGuard;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class NumberReservationTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $staff;
    private $unit;
    private $docType;
    private $format;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role'      => 'admin',
            'is_active' => true,
        ]);

        $this->staff = User::factory()->create([
            'role'      => 'staff',
            'is_active' => true,
        ]);

        $this->unit = Unit::create([
            'name'        => 'Test Unit',
            'slug'        => 'test-unit',
            'code'        => 'TEST',
            'description' => 'Test Unit Description',
        ]);

        // Attach unit to staff so policy checks can pass if needed
        $this->staff->units()->attach($this->unit->id);
        $this->admin->units()->attach($this->unit->id);

        $this->docType = DocumentType::create([
            'unit_id' => $this->unit->id,
            'code'    => 'TEST',
            'name'    => 'Test Document Type',
            'description' => 'Test Description',
        ]);

        $this->format = LetterFormat::where('type', 'outgoing')->first();
    }

    /**
     * Test admin approval generates exact N slots.
     */
    public function test_admin_approval_generates_exact_n_slots()
    {
        // Establish timeline:
        // Mail 1: sequence 1, date 2026-05-10
        // Mail 2: sequence 3, date 2026-05-20 (leaving sequence 2 vacant)
        Mail::create([
            'reference_number' => 'TEST/001/V/2026',
            'type'             => 'outgoing',
            'document_type_id' => $this->docType->id,
            'unit_id'          => $this->unit->id,
            'sequence_number'  => 1,
            'sender_name'      => 'Sender',
            'recipient_name'   => 'Recipient',
            'subject'          => 'Subject 1',
            'body'             => 'Body 1',
            'tanggal_surat'    => Carbon::parse('2026-05-10'),
            'status'           => 'completed',
            'created_by'       => $this->admin->id,
        ]);

        Mail::create([
            'reference_number' => 'TEST/003/V/2026',
            'type'             => 'outgoing',
            'document_type_id' => $this->docType->id,
            'unit_id'          => $this->unit->id,
            'sequence_number'  => 3,
            'sender_name'      => 'Sender',
            'recipient_name'   => 'Recipient',
            'subject'          => 'Subject 3',
            'body'             => 'Body 3',
            'tanggal_surat'    => Carbon::parse('2026-05-20'),
            'status'           => 'completed',
            'created_by'       => $this->admin->id,
        ]);

        // Create reservation request for 1 slot at 2026-05-15 (should fit sequence 2)
        $reservation = NumberReservation::create([
            'letter_format_id' => $this->format->id,
            'document_type_id' => $this->docType->id,
            'requested_by'     => $this->staff->id,
            'quantity'         => 1,
            'status'           => 'pending',
            'reserved_slots'   => [],
            'backdate_target'  => Carbon::parse('2026-05-15'),
            'reason'           => 'Testing reservation',
        ]);

        // Approve reservation as admin
        $response = $this->actingAs($this->admin)->post(route('number_reservations.approve', $reservation->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $reservation->refresh();
        $this->assertEquals('approved', $reservation->status);
        $this->assertEquals($this->admin->id, $reservation->approved_by);
        $this->assertCount(1, $reservation->reserved_slots);

        $slot = $reservation->reserved_slots[0];
        $this->assertEquals(2, $slot['sequence_number']);
        $this->assertEquals('2026-05-15', $slot['date']);

        // Assert mail record is created with correct sequence and date_locked
        $mail = Mail::findOrFail($slot['mail_id']);
        $this->assertEquals(2, $mail->sequence_number);
        $this->assertEquals('2026-05-15', $mail->tanggal_surat->format('Y-m-d'));
        $this->assertTrue($mail->date_locked);
        $this->assertEquals('draft', $mail->status);
        $this->assertEquals($reservation->id, $mail->reservation_slot_id);
    }

    /**
     * Test ChronologicalGuard rejects invalid backdate requests.
     */
    public function test_chronological_guard_rejects_invalid_backdate_requests()
    {
        // Establish timeline:
        // Mail 1: sequence 1, date 2026-05-10
        // Mail 2: sequence 3, date 2026-05-20 (leaving sequence 2 vacant)
        Mail::create([
            'reference_number' => 'TEST/001/V/2026',
            'type'             => 'outgoing',
            'document_type_id' => $this->docType->id,
            'unit_id'          => $this->unit->id,
            'sequence_number'  => 1,
            'sender_name'      => 'Sender',
            'recipient_name'   => 'Recipient',
            'subject'          => 'Subject 1',
            'body'             => 'Body 1',
            'tanggal_surat'    => Carbon::parse('2026-05-10'),
            'status'           => 'completed',
            'created_by'       => $this->admin->id,
        ]);

        Mail::create([
            'reference_number' => 'TEST/003/V/2026',
            'type'             => 'outgoing',
            'document_type_id' => $this->docType->id,
            'unit_id'          => $this->unit->id,
            'sequence_number'  => 3,
            'sender_name'      => 'Sender',
            'recipient_name'   => 'Recipient',
            'subject'          => 'Subject 3',
            'body'             => 'Body 3',
            'tanggal_surat'    => Carbon::parse('2026-05-20'),
            'status'           => 'completed',
            'created_by'       => $this->admin->id,
        ]);

        // Create reservation request for 1 slot at 2026-05-05 (which violates chronological constraint: 2026-05-05 < 2026-05-10)
        $reservation = NumberReservation::create([
            'letter_format_id' => $this->format->id,
            'document_type_id' => $this->docType->id,
            'requested_by'     => $this->staff->id,
            'quantity'         => 1,
            'status'           => 'pending',
            'reserved_slots'   => [],
            'backdate_target'  => Carbon::parse('2026-05-05'),
            'reason'           => 'Testing invalid reservation',
        ]);

        // Approve reservation as admin - should fail chronological guard check
        $response = $this->actingAs($this->admin)->post(route('number_reservations.approve', $reservation->id));

        $response->assertSessionHasErrors(['backdate_target']);
        $reservation->refresh();
        $this->assertEquals('pending', $reservation->status);
        $this->assertEmpty($reservation->reserved_slots);
    }

    /**
     * Test Staff cannot change the date of a locked reserved slot.
     */
    public function test_staff_cannot_change_the_date_of_a_locked_reserved_slot()
    {
        // Approve a reservation and generate a locked slot
        $reservation = NumberReservation::create([
            'letter_format_id' => $this->format->id,
            'document_type_id' => $this->docType->id,
            'requested_by'     => $this->staff->id,
            'quantity'         => 1,
            'status'           => 'pending',
            'reserved_slots'   => [],
            'backdate_target'  => Carbon::parse('2026-05-15'),
            'reason'           => 'Testing fillSlot',
        ]);

        // Approve it first to generate the mail slot
        $this->actingAs($this->admin)->post(route('number_reservations.approve', $reservation->id));
        $reservation->refresh();
        $slot = $reservation->reserved_slots[0];
        $mail = Mail::findOrFail($slot['mail_id']);

        $this->assertTrue($mail->date_locked);

        // Try to update/fill slot, but passing a different tanggal_surat
        $response = $this->actingAs($this->staff)->put(
            route('number_reservations.fill_slot', ['reservation' => $reservation->id, 'mail' => $mail->id]),
            [
                'subject'              => 'Actual Subject',
                'body'                 => 'Actual Body',
                'sender_name'          => 'Actual Sender',
                'recipient_name'       => 'Actual Recipient',
                'tanggal_surat'         => '2026-05-18', // attempting to change date
                'priority'              => 'normal',
                'classification'        => 'open',
            ]
        );

        $response->assertSessionHasErrors(['tanggal_surat']);

        // Assert that the mail record is NOT changed in database
        $mail->refresh();
        $this->assertEquals('2026-05-15', $mail->tanggal_surat->format('Y-m-d'));
        $this->assertTrue($mail->date_locked);

        // Try to fill slot with the correct date / no date change
        $response2 = $this->actingAs($this->staff)->put(
            route('number_reservations.fill_slot', ['reservation' => $reservation->id, 'mail' => $mail->id]),
            [
                'subject'              => 'Actual Subject',
                'body'                 => 'Actual Body',
                'sender_name'          => 'Actual Sender',
                'recipient_name'       => 'Actual Recipient',
                'tanggal_surat'         => '2026-05-15', // correct date
                'priority'              => 'normal',
                'classification'        => 'open',
            ]
        );

        $response2->assertRedirect();
        $mail->refresh();
        $this->assertEquals('Actual Subject', $mail->subject);
        $this->assertEquals('Actual Body', $mail->body);
        $this->assertEquals('Actual Sender', $mail->sender_name);
        $this->assertEquals('Actual Recipient', $mail->recipient_name);
        $this->assertEquals('2026-05-15', $mail->tanggal_surat->format('Y-m-d'));
        $this->assertTrue($mail->date_locked); // date_locked must remain true
        $this->assertEquals('pending', $mail->status); // status becomes pending
    }
}
