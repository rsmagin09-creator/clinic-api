<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\MedicalService;
use App\Models\Schedule;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PriceApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_saves_doctor_price_in_tenge(): void
    {
        $doctor = Doctor::factory()->create(['price' => 7500]);
        foreach (range(1, 7) as $weekday) {
            Schedule::factory()->create(['doctor_id' => $doctor->id, 'weekday' => $weekday,
                'start_time' => '08:00', 'end_time' => '14:00', 'slot_minutes' => 30]);
        }
        Sanctum::actingAs(User::factory()->patient()->create());

        $this->postJson('/api/v1/appointments', [
            'doctor_id' => $doctor->id, 'date' => now()->addDays(2)->toDateString(), 'time' => '10:00',
        ])->assertStatus(201)
            ->assertJsonPath('data.price', 7500)
            ->assertJsonPath('data.price_formatted', '7 500 ₸');

        // смена цены врача не меняет цену уже созданной записи
        $doctor->update(['price' => 9000]);
        $this->assertDatabaseHas('appointments', ['doctor_id' => $doctor->id, 'price' => 7500]);
    }

    public function test_price_list_is_public_and_filtered_by_specialty(): void
    {
        $cardio = Specialty::factory()->create(['name' => 'Кардиолог']);
        MedicalService::factory()->create(['specialty_id' => $cardio->id, 'name' => 'ЭКГ', 'price' => 4000]);
        MedicalService::factory()->create(['name' => 'Общий анализ крови', 'price' => 2500]);

        $this->getJson('/api/v1/services?specialty_id='.$cardio->id)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.price_formatted', '4 000 ₸');
    }

    public function test_only_admin_can_change_prices_and_price_is_validated(): void
    {
        Sanctum::actingAs(User::factory()->patient()->create());
        $this->postJson('/api/v1/services', ['name' => 'УЗИ', 'price' => 9000])->assertStatus(403);

        Sanctum::actingAs(User::factory()->admin()->create());
        $this->postJson('/api/v1/services', ['name' => 'УЗИ', 'price' => -5])
            ->assertStatus(422)->assertJsonValidationErrors('price');
        $this->postJson('/api/v1/services', ['name' => 'УЗИ', 'price' => 9000])
            ->assertStatus(201)->assertJsonPath('data.price_formatted', '9 000 ₸');
    }
}
