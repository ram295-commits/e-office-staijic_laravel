<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\DocumentType;
use App\Models\Unit;
use App\Models\Mail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class MailImportTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'is_active' => true,
        ]);
    }

    public function test_import_page_is_accessible_to_authenticated_user()
    {
        $response = $this->actingAs($this->user)->get(route('mails.import.index'));

        $response->assertStatus(200);
        $response->assertSee('Import Data Surat');
    }

    public function test_template_download_is_accessible()
    {
        $response = $this->actingAs($this->user)->get(route('mails.import.template'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_import_validation_fails_without_file()
    {
        $response = $this->actingAs($this->user)->post(route('mails.import.store'), []);

        $response->assertSessionHasErrors(['excel_file']);
    }
}
