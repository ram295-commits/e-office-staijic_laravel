<?php

namespace Tests\Feature;

use App\Models\DocumentType;
use App\Models\Mail;
use App\Models\MailContentLog;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MailObserverTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $unit;
    private $docType;
    private $mail;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->unit = Unit::create([
            'name' => 'Test Unit',
            'slug' => 'test-unit',
            'description' => 'Test Unit Description',
            'code' => 'TEST',
        ]);

        $this->docType = DocumentType::create([
            'unit_id' => $this->unit->id,
            'code' => 'TEST',
            'name' => 'Test Document Type',
            'description' => 'Test Description',
        ]);

        $this->mail = Mail::create([
            'reference_number' => 'TEST/OBSERVER/001',
            'type' => 'incoming',
            'document_type_id' => $this->docType->id,
            'unit_id' => $this->unit->id,
            'sequence_number' => 10,
            'subject' => 'Original Subject',
            'body' => 'Original Body',
            'sender_name' => 'Original Sender',
            'recipient_name' => 'Original Recipient',
            'tanggal_surat' => '2026-05-10',
            'priority' => 'normal',
            'classification' => 'open',
            'status' => 'pending',
            'created_by' => $this->user->id,
        ]);
    }

    /**
     * Test updating a whitelisted field triggers a log entry and saves changes/old_values.
     */
    public function test_updating_whitelisted_field_creates_log()
    {
        $this->actingAs($this->user);

        // Update whitelisted field
        $this->mail->update([
            'subject' => 'New Subject',
            'body'    => 'New Body',
        ]);

        // Assert log was created
        $this->assertDatabaseHas('mail_content_logs', [
            'mail_id' => $this->mail->id,
            'changed_by' => $this->user->id,
            'action' => 'update',
        ]);

        $log = MailContentLog::where('mail_id', $this->mail->id)->first();
        $this->assertNotNull($log);

        // Ensure ONLY whitelisted columns are logged
        $this->assertEquals(['subject' => 'New Subject', 'body' => 'New Body'], $log->changes);
        $this->assertEquals(['subject' => 'Original Subject', 'body' => 'Original Body'], $log->old_values);
    }

    /**
     * Test updating an un-whitelisted field does NOT trigger a log entry.
     */
    public function test_updating_unwhitelisted_field_does_not_create_log()
    {
        $this->actingAs($this->user);

        // Update unwhitelisted fields (sender_name, recipient_name, priority)
        $this->mail->update([
            'sender_name' => 'New Sender',
            'recipient_name' => 'New Recipient',
            'priority' => 'urgent',
        ]);

        // Assert no log was created
        $this->assertDatabaseMissing('mail_content_logs', [
            'mail_id' => $this->mail->id,
        ]);
    }

    /**
     * Test that date column (tanggal_surat) is correctly mapped to "date" and whitelisted.
     */
    public function test_updating_date_column_maps_to_date_in_log()
    {
        $this->actingAs($this->user);

        // Update date (tanggal_surat)
        $this->mail->update([
            'tanggal_surat' => '2026-05-15',
        ]);

        // Assert log was created
        $this->assertDatabaseHas('mail_content_logs', [
            'mail_id' => $this->mail->id,
        ]);

        $log = MailContentLog::where('mail_id', $this->mail->id)->first();
        $this->assertNotNull($log);

        // Ensure "tanggal_surat" is logged as "date"
        $this->assertEquals(['date' => '2026-05-15'], $log->changes);
        $this->assertEquals(['date' => '2026-05-10'], $log->old_values);
    }

    /**
     * Test afterCommit functionality: Log is only inserted if the transaction commits.
     */
    public function test_log_is_created_only_after_transaction_commits()
    {
        $this->actingAs($this->user);

        // Scenario 1: Commit Transaction
        DB::transaction(function () {
            $this->mail->update([
                'subject' => 'Subject Committed',
            ]);

            // Inside transaction, DB::afterCommit callbacks have not run yet
            $this->assertDatabaseMissing('mail_content_logs', [
                'mail_id' => $this->mail->id,
            ]);
        });

        // After transaction commits, the log is created
        $this->assertDatabaseHas('mail_content_logs', [
            'mail_id' => $this->mail->id,
            'changes' => json_encode(['subject' => 'Subject Committed']),
        ]);

        // Clear logs
        MailContentLog::truncate();

        // Scenario 2: Rollback Transaction
        DB::beginTransaction();
        
        $this->mail->update([
            'subject' => 'Subject Rolled Back',
        ]);

        DB::rollBack();

        // The log is never created because the transaction was rolled back
        $this->assertDatabaseMissing('mail_content_logs', [
            'mail_id' => $this->mail->id,
        ]);
    }
}
