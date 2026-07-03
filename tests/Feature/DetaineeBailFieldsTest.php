<?php

namespace Tests\Feature;

use App\Models\Facility;
use App\Models\PenaltyReference;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DetaineeBailFieldsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_detainee_with_bail_fields()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $facility = Facility::create([
            'name' => 'Investment Facility',
            'region' => 'Region 1',
            'address' => '1 Main St',
            'capacity' => 100,
        ]);
        $penalty = PenaltyReference::create([
            'rpc_code' => '003',
            'charge_name' => 'Test Charge',
            'max_penalty_years' => 1,
            'max_penalty_months' => 0,
            'law_source' => 'RPC',
        ]);

        $response = $this->actingAs($admin)->post(route('detainees.store'), [
            'full_name' => 'Bail Feature Test',
            'charge_description' => 'Test description',
            'charge_rpc_code' => $penalty->id,
            'commitment_date' => now()->subDay()->format('Y-m-d'),
            'facility_id' => $facility->id,
            'bail_amount' => 45000,
            'bail_status' => 'pending_review',
            'bail_posted_at' => now()->subDay()->format('Y-m-d'),
            'bail_notes' => 'Requires special bail assessment.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('detainees', [
            'full_name' => 'Bail Feature Test',
            'bail_amount' => 45000,
            'bail_status' => 'pending_review',
        ]);
    }
}
