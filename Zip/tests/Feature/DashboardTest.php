<?php

namespace Tests\Feature;

use App\Models\DocumentType;
use App\Models\Disposition;
use App\Models\Mail;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_guest_redirected_to_login()
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_dashboard_accessible_to_authenticated_users()
    {
        $user = User::factory()->create([
            'role' => 'staff',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertViewHasAll(['stats', 'recentMails', 'myDispositions', 'users', 'monthlyStats']);
    }

    public function test_dashboard_data_isolation_for_staff()
    {
        // 1. Create units
        $unitA = Unit::create(['name' => 'Unit A', 'slug' => 'unit-a', 'code' => 'UA']);
        $unitB = Unit::create(['name' => 'Unit B', 'slug' => 'unit-b', 'code' => 'UB']);

        // 2. Create users
        $staff = User::factory()->create([
            'role' => 'staff',
            'is_active' => true,
        ]);
        // Attach Staff to Unit A only
        $staff->units()->attach($unitA->id);

        $otherUser = User::factory()->create([
            'role' => 'staff',
            'is_active' => true,
        ]);
        $otherUser->units()->attach($unitB->id);

        // Document types
        $docTypeA = DocumentType::create(['unit_id' => $unitA->id, 'code' => 'TA', 'name' => 'Type A']);
        $docTypeB = DocumentType::create(['unit_id' => $unitB->id, 'code' => 'TB', 'name' => 'Type B']);

        // 3. Create mails
        // Mail 1: Created by staff (in Unit A)
        $mailCreatedByStaff = Mail::create([
            'reference_number' => 'REF001',
            'type' => 'incoming',
            'document_type_id' => $docTypeA->id,
            'unit_id' => $unitA->id,
            'subject' => 'Mail 1: Created by Staff',
            'body' => 'Body 1',
            'sender_name' => 'Sender',
            'recipient_name' => 'Recipient',
            'tanggal_surat' => '2026-06-01',
            'priority' => 'normal',
            'classification' => 'open',
            'created_by' => $staff->id,
        ]);

        // Mail 2: In Unit A, created by otherUser, not assigned to staff
        $mailInStaffUnit = Mail::create([
            'reference_number' => 'REF002',
            'type' => 'incoming',
            'document_type_id' => $docTypeA->id,
            'unit_id' => $unitA->id,
            'subject' => 'Mail 2: In Staff Unit A',
            'body' => 'Body 2',
            'sender_name' => 'Sender',
            'recipient_name' => 'Recipient',
            'tanggal_surat' => '2026-06-01',
            'priority' => 'normal',
            'classification' => 'open',
            'created_by' => $otherUser->id,
        ]);

        // Mail 3: In Unit B, created by otherUser, but assigned to staff
        $mailAssignedToStaff = Mail::create([
            'reference_number' => 'REF003',
            'type' => 'incoming',
            'document_type_id' => $docTypeB->id,
            'unit_id' => $unitB->id,
            'subject' => 'Mail 3: Assigned to Staff',
            'body' => 'Body 3',
            'sender_name' => 'Sender',
            'recipient_name' => 'Recipient',
            'tanggal_surat' => '2026-06-01',
            'priority' => 'normal',
            'classification' => 'open',
            'created_by' => $otherUser->id,
            'assigned_to' => $staff->id,
        ]);

        // Mail 4: In Unit B, created by otherUser, has disposition for staff
        $mailWithDispositionForStaff = Mail::create([
            'reference_number' => 'REF004',
            'type' => 'incoming',
            'document_type_id' => $docTypeB->id,
            'unit_id' => $unitB->id,
            'subject' => 'Mail 4: Has Disposition for Staff',
            'body' => 'Body 4',
            'sender_name' => 'Sender',
            'recipient_name' => 'Recipient',
            'tanggal_surat' => '2026-06-01',
            'priority' => 'normal',
            'classification' => 'open',
            'created_by' => $otherUser->id,
        ]);

        Disposition::create([
            'mail_id' => $mailWithDispositionForStaff->id,
            'from_user_id' => $otherUser->id,
            'to_user_id' => $staff->id,
            'instruction' => 'Instruksi',
            'action_type' => 'for_action',
            'status' => 'pending',
        ]);

        // Mail 5: In Unit B, created by otherUser, not assigned, no disposition (Staff should NOT see this)
        $mailHidden = Mail::create([
            'reference_number' => 'REF005',
            'type' => 'incoming',
            'document_type_id' => $docTypeB->id,
            'unit_id' => $unitB->id,
            'subject' => 'Mail 5: Hidden from Staff',
            'body' => 'Body 5',
            'sender_name' => 'Sender',
            'recipient_name' => 'Recipient',
            'tanggal_surat' => '2026-06-01',
            'priority' => 'normal',
            'classification' => 'open',
            'created_by' => $otherUser->id,
        ]);

        // Acting as staff, request dashboard
        $response = $this->actingAs($staff)->get('/dashboard');
        $response->assertStatus(200);

        $recentMails = $response->viewData('recentMails');
        
        // Assertions
        $mailIds = collect($recentMails)->pluck('id');

        $this->assertTrue($mailIds->contains($mailCreatedByStaff->id), 'Staff should see mail created by them.');
        $this->assertTrue($mailIds->contains($mailInStaffUnit->id), 'Staff should see mail in their unit.');
        $this->assertTrue($mailIds->contains($mailAssignedToStaff->id), 'Staff should see mail assigned to them.');
        $this->assertTrue($mailIds->contains($mailWithDispositionForStaff->id), 'Staff should see mail they received a disposition for.');
        $this->assertFalse($mailIds->contains($mailHidden->id), 'Staff should NOT see mail that they are not authorized for.');
    }
}
