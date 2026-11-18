<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Schedule;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AppointmentApiTest extends TestCase
{
    use RefreshDatabase;

    private Doctor $doctor;

    private User $patient;

    private string $date;

    protected function setUp(): void
    {
        parent::setUp();

        // Врач работает каждый день с 08:00 до 14:00, приём по 30 минут
        $this->doctor = Doctor::factory()->create();
        foreach (range(1, 7) as $weekday) {
            Schedule::factory()->create([
                'doctor_id' => $this->doctor->id, 'weekday' => $weekday,
                'start_time' => '08:00', 'end_time' => '14:00', 'slot_minutes' => 30,
            ]);
        }
        $this->patient = User::factory()->patient()->create();
        $this->date = now()->addDays(2)->toDateString();
    }

    private function book(array $override = [])
    {
        return $this->postJson('/api/v1/appointments', array_merge([
            'doctor_id' => $this->doctor->id, 'date' => $this->date, 'time' => '10:00', 'reason' => 'Осмотр',
        ], $override));
    }

    public function test_patient_can_book_free_slot(): void
    {
        Sanctum::actingAs($this->patient);

        $this->book()
            ->assertStatus(201)
            ->assertJsonPath('data.status', 'scheduled')
            ->assertJsonPath('data.patient.id', $this->patient->id);

        $this->assertDatabaseHas('appointments', [
            'user_id' => $this->patient->id, 'doctor_id' => $this->doctor->id, 'status' => 'scheduled',
        ]);
    }

    public function test_cannot_book_busy_time(): void
    {
        Appointment::factory()->create([
            'doctor_id' => $this->doctor->id, 'date' => $this->date, 'time' => '10:00',
        ]);
        Sanctum::actingAs($this->patient);

        $this->book()
            ->assertStatus(409)
            ->assertJsonPath('message', 'Это время у врача уже занято. Выберите другое время');

        $this->assertDatabaseCount('appointments', 1);
    }

    public function test_cannot_book_outside_schedule(): void
    {
        Sanctum::actingAs($this->patient);

        $this->book(['time' => '23:00'])->assertStatus(409);
        $this->book(['time' => '10:10'])->assertStatus(409); // не начало интервала
    }

    public function test_only_one_appointment_to_same_doctor_per_day(): void
    {
        Sanctum::actingAs($this->patient);

        $this->book()->assertStatus(201);
        $this->book(['time' => '11:00'])
            ->assertStatus(409)
            ->assertJsonPath('message', 'У вас уже есть запись к этому врачу на этот день');
    }

    public function test_booking_validation_errors(): void
    {
        Sanctum::actingAs($this->patient);

        $this->book(['doctor_id' => 999, 'date' => '2020-01-01', 'time' => '9 утра'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['doctor_id', 'date', 'time']);
    }

    public function test_patient_cannot_view_foreign_appointment(): void
    {
        $foreign = Appointment::factory()->create(['doctor_id' => $this->doctor->id]);
        Sanctum::actingAs($this->patient);

        $this->getJson("/api/v1/appointments/{$foreign->id}")->assertStatus(403);
    }

    public function test_patient_sees_only_own_appointments(): void
    {
        Appointment::factory()->count(3)->create(['doctor_id' => $this->doctor->id]);
        Appointment::factory()->create(['doctor_id' => $this->doctor->id, 'user_id' => $this->patient->id]);
        Sanctum::actingAs($this->patient);

        $this->getJson('/api/v1/appointments')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.total', 1);
    }

    public function test_status_changes_only_by_allowed_transitions(): void
    {
        $appointment = Appointment::factory()->create(['doctor_id' => $this->doctor->id]);
        Sanctum::actingAs($this->doctor->user);

        $this->patchJson("/api/v1/appointments/{$appointment->id}/status", ['status' => 'completed'])
            ->assertOk()
            ->assertJsonPath('data.status', 'completed');

        $this->patchJson("/api/v1/appointments/{$appointment->id}/status", ['status' => 'no_show'])
            ->assertStatus(409);

        $this->assertDatabaseHas('appointments', ['id' => $appointment->id, 'status' => 'completed']);
    }

    public function test_medical_record_only_after_completed_appointment(): void
    {
        $appointment = Appointment::factory()->create([
            'doctor_id' => $this->doctor->id, 'status' => AppointmentStatus::Scheduled,
        ]);
        Sanctum::actingAs($this->doctor->user);
        $url = "/api/v1/appointments/{$appointment->id}/medical-record";

        $this->postJson($url, ['diagnosis' => 'ОРВИ'])->assertStatus(409);

        $appointment->update(['status' => AppointmentStatus::Completed]);
        $this->postJson($url, ['diagnosis' => 'ОРВИ', 'prescription' => 'Покой'])->assertStatus(201);

        $this->assertDatabaseHas('medical_records', ['appointment_id' => $appointment->id, 'diagnosis' => 'ОРВИ']);
    }

    public function test_doctors_list_filter_sort_and_pagination(): void
    {
        $therapist = Specialty::factory()->create(['name' => 'Терапевт']);
        Doctor::factory()->count(4)->create()->each(fn ($d) => $d->specialties()->attach($therapist));

        $this->getJson("/api/v1/doctors?specialty_id={$therapist->id}&sort=experience_years&order=desc&per_page=3&page=2")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.total', 4)
            ->assertJsonPath('meta.per_page', 3)
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.last_page', 2);

        $this->getJson('/api/v1/doctors?sort=password')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['sort']);
    }
}
