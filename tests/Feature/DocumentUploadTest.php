<?php

namespace Tests\Feature;

use App\Models\Detainee;
use App\Models\Facility;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_upload_document_for_detainee()
    {
        Storage::fake('local');

        $admin = User::factory()->create(['role' => 'admin']);

        $facility = Facility::create([
            'name' => 'Test Facility',
            'region' => 'Region X',
            'address' => '123 Test St',
            'capacity' => 10,
        ]);

        $detainee = Detainee::create([
            'full_name' => 'Upload Test Detainee',
            'charge_description' => 'Test charge',
            'commitment_date' => now()->subDays(2)->format('Y-m-d'),
            'facility_id' => $facility->id,
            'status' => 'detained',
            'created_by' => $admin->id,
        ]);

        $file = UploadedFile::fake()->create('test.pdf', 100, 'application/pdf');

        $response = $this->actingAs($admin)->post(route('detainees.documents.store', $detainee), [
            'file' => $file,
            'doc_type' => 'court_record',
            'phase_number' => null,
        ]);

        $response->assertRedirect();

        // Assert file stored in the configured 'local' disk
        Storage::disk('local')->assertExists('documents/' . $detainee->id . '/' . $file->hashName());

        $this->assertDatabaseHas('documents', [
            'detainee_id' => $detainee->id,
            'doc_type' => 'court_record',
        ]);
    }
}
