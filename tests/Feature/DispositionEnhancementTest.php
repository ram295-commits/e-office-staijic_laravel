<?php

namespace Tests\Feature;

use App\Models\DocumentType;
use App\Models\Disposition;
use App\Models\Mail;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DispositionEnhancementTest extends TestCase
{
    use RefreshDatabase;

    private $manager;
    private $staff1;
    private $staff2;
    private $mail;

    protected function setUp(): void
    {
        parent::setUp();

        // Create unit
        $unit = Unit::create([
            'name' => 'Unit Kepegawaian',
            'slug' => 'unit-kepegawaian',
            'code' => 'KP',
            'description' => 'Unit Kepegawaian JIC',
        ]);

        // Create users
        $this->manager = User::factory()->create([
            'role' => 'manager',
            'is_active' => true,
        ]);
        $this->staff1 = User::factory()->create([
            'role' => 'staff',
            'is_active' => true,
        ]);
        $this->staff2 = User::factory()->create([
            'role' => 'staff',
            'is_active' => true,
        ]);

        $this->manager->units()->attach($unit->id);
        $this->staff1->units()->attach($unit->id);
        $this->staff2->units()->attach($unit->id);

        $docType = DocumentType::create([
            'unit_id' => $unit->id,
            'code' => 'KP',
            'name' => 'Surat Keputusan',
        ]);

        // Create mail
        $this->mail = Mail::create([
            'reference_number' => '001/KP/VI/2026',
            'type' => 'incoming',
            'document_type_id' => $docType->id,
            'unit_id' => $unit->id,
            'sequence_number' => 1,
            'sender_name' => 'UAT Sender',
            'recipient_name' => 'UAT Recipient',
            'subject' => 'Surat UAT',
            'body' => 'Rincian surat UAT',
            'tanggal_surat' => now(),
            'status' => 'pending',
            'created_by' => $this->manager->id,
        ]);
    }

    public function test_multi_recipient_disposition_creation()
    {
        $this->actingAs($this->manager);

        $response = $this->post(route('dispositions.store'), [
            'mail_id' => $this->mail->id,
            'to_user_ids' => [$this->staff1->id, $this->staff2->id],
            'instruction' => 'Silakan diproses bersama.',
            'action_type' => 'for_action',
            'due_date' => now()->addDays(2)->format('Y-m-d'),
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        // Check that two disposition records were created
        $this->assertDatabaseCount('dispositions', 2);
        $this->assertDatabaseHas('dispositions', [
            'mail_id' => $this->mail->id,
            'from_user_id' => $this->manager->id,
            'to_user_id' => $this->staff1->id,
            'instruction' => 'Silakan diproses bersama.',
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('dispositions', [
            'mail_id' => $this->mail->id,
            'from_user_id' => $this->manager->id,
            'to_user_id' => $this->staff2->id,
            'instruction' => 'Silakan diproses bersama.',
            'status' => 'pending',
        ]);

        // Check mail status updated to in_progress
        $this->mail->refresh();
        $this->assertEquals('in_progress', $this->mail->status);
    }

    public function test_respond_to_disposition_with_file_attachment()
    {
        Storage::fake('public');

        $this->actingAs($this->manager);

        // First, create a disposition
        $disposition = Disposition::create([
            'mail_id' => $this->mail->id,
            'from_user_id' => $this->manager->id,
            'to_user_id' => $this->staff1->id,
            'instruction' => 'Bantu buat laporan.',
            'action_type' => 'for_reply',
            'status' => 'pending',
        ]);

        $this->actingAs($this->staff1);

        $file = UploadedFile::fake()->create('proof_laporan.pdf', 500); // 500 KB

        $response = $this->post(route('dispositions.respond', $disposition->id), [
            'response_notes' => 'Laporan sudah selesai dibuat dan dilampirkan.',
            'status' => 'completed',
            'response_attachment' => $file,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $disposition->refresh();
        $this->assertEquals('completed', $disposition->status);
        $this->assertEquals('Laporan sudah selesai dibuat dan dilampirkan.', $disposition->response_notes);
        $this->assertNotNull($disposition->response_attachment_path);
        $this->assertEquals('proof_laporan.pdf', $disposition->response_attachment_name);

        // Verify file was stored on public disk
        Storage::disk('public')->assertExists($disposition->response_attachment_path);
    }
}
