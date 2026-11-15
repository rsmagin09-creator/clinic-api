<?php

namespace Tests\Feature;

use App\Models\Specialty;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SpecialtyApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_list_specialties(): void
    {
        Specialty::factory()->count(3)->create();

        $this->getJson('/api/v1/specialties')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_admin_can_create_specialty(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $this->postJson('/api/v1/specialties', ['name' => 'Педиатр', 'description' => 'Приём детей'])
            ->assertStatus(201)
            ->assertJsonPath('data.name', 'Педиатр');

        $this->assertDatabaseHas('specialties', ['name' => 'Педиатр']);
    }

    public function test_patient_cannot_create_specialty(): void
    {
        Sanctum::actingAs(User::factory()->patient()->create());

        $this->postJson('/api/v1/specialties', ['name' => 'Педиатр'])
            ->assertStatus(403);

        $this->assertDatabaseMissing('specialties', ['name' => 'Педиатр']);
    }

    public function test_specialty_not_found_returns_404(): void
    {
        $this->getJson('/api/v1/specialties/999')
            ->assertStatus(404)
            ->assertJsonPath('message', 'Специальность не найдена');
    }
}
