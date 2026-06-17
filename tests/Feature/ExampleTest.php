<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    public function test_letter_number_uses_unit_code_instead_of_doc_type_code(): void
    {
        $user = \App\Models\User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $unit = \App\Models\Unit::create([
            'name' => 'Program Studi',
            'slug' => 'prodi-pendidikan-hukum',
            'code' => 'PRODI',
        ]);

        $docType = \App\Models\DocumentType::create([
            'unit_id' => $unit->id,
            'code' => 'SK',
            'name' => 'Surat Keputusan',
        ]);

        // Attach unit to user
        $user->units()->attach($unit->id);

        $this->actingAs($user);

        $response = $this->post(route('mails.store'), [
            'document_type_id' => $docType->id,
            'type' => 'outgoing',
            'subject' => 'Test Subject',
            'body' => 'Test Body',
            'sender_name' => 'Sender',
            'recipient_name' => 'Recipient',
            'tanggal_surat' => '2026-05-31',
            'priority' => 'normal',
            'classification' => 'open',
        ]);

        $response->assertRedirect();
        
        $mail = \App\Models\Mail::latest()->first();
        $this->assertEquals('001/SK/PRODI/STAI-JIC/V/2026', $mail->reference_number);
    }
}
