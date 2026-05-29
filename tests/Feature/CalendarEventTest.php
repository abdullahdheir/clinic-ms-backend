<?php

namespace Tests\Feature;

use App\Models\CalendarEvent;
use App\Models\Doctor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class CalendarEventTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'patient', 'guard_name' => 'web']);
        Role::create(['name' => 'doctor', 'guard_name' => 'web']);
    }

    /**
     * Test that calendar events can be listed within a date range.
     *
     * @return void
     */
    public function test_can_list_calendar_events_in_date_range(): void
    {
        $doctorUser = User::factory()->create(['role' => 'doctor']);
        $doctorUser->assignRole('doctor');
        $doctor = Doctor::factory()->create(['user_id' => $doctorUser->id]);

        $startDate = now()->format('Y-m-d');
        $endDate = now()->addWeek()->format('Y-m-d');

        CalendarEvent::factory()->count(3)->create([
            'doctor_id' => $doctor->id,
            'start_time' => now(),
            'end_time' => now()->addHour(),
        ]);

        $response = $this->actingAs($doctorUser)->getJson("/api/calendar?start={$startDate}&end={$endDate}");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /**
     * Test that a calendar event can be created.
     *
     * @return void
     */
    public function test_can_create_calendar_event(): void
    {
        $doctorUser = User::factory()->create(['role' => 'doctor']);
        $doctorUser->assignRole('doctor');
        $doctor = Doctor::factory()->create(['user_id' => $doctorUser->id]);

        $startTime = now()->format('Y-m-d H:i:s');
        $endTime = now()->addHour()->format('Y-m-d H:i:s');

        $response = $this->actingAs($doctorUser)->postJson('/api/calendar', [
            'title' => 'Team Meeting',
            'description' => 'Weekly team sync',
            'start_time' => $startTime,
            'end_time' => $endTime,
            'event_type' => 'meeting',
            'doctor_id' => $doctor->id,
            'color' => '#3B82C4',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.title', 'Team Meeting')
            ->assertJsonPath('data.event_type', 'meeting');

        $this->assertDatabaseHas('calendar_events', [
            'title' => 'Team Meeting',
            'event_type' => 'meeting',
        ]);
    }

    /**
     * Test that a calendar event can be shown.
     *
     * @return void
     */
    public function test_can_show_calendar_event(): void
    {
        $doctorUser = User::factory()->create(['role' => 'doctor']);
        $doctorUser->assignRole('doctor');
        $doctor = Doctor::factory()->create(['user_id' => $doctorUser->id]);

        $event = CalendarEvent::factory()->create([
            'doctor_id' => $doctor->id,
            'title' => 'Patient Consultation',
        ]);

        $response = $this->actingAs($doctorUser)->getJson("/api/calendar/{$event->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'Patient Consultation');
    }

    /**
     * Test that a calendar event can be updated.
     *
     * @return void
     */
    public function test_can_update_calendar_event(): void
    {
        $doctorUser = User::factory()->create(['role' => 'doctor']);
        $doctorUser->assignRole('doctor');
        $doctor = Doctor::factory()->create(['user_id' => $doctorUser->id]);

        $event = CalendarEvent::factory()->create([
            'doctor_id' => $doctor->id,
            'title' => 'Old Title',
        ]);

        $response = $this->actingAs($doctorUser)->patchJson("/api/calendar/{$event->id}", [
            'title' => 'Updated Title',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'Updated Title');

        $this->assertDatabaseHas('calendar_events', [
            'id' => $event->id,
            'title' => 'Updated Title',
        ]);
    }

    /**
     * Test that a calendar event can be deleted.
     *
     * @return void
     */
    public function test_can_delete_calendar_event(): void
    {
        $doctorUser = User::factory()->create(['role' => 'doctor']);
        $doctorUser->assignRole('doctor');
        $doctor = Doctor::factory()->create(['user_id' => $doctorUser->id]);

        $event = CalendarEvent::factory()->create([
            'doctor_id' => $doctor->id,
        ]);

        $response = $this->actingAs($doctorUser)->deleteJson("/api/calendar/{$event->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('calendar_events', [
            'id' => $event->id,
        ]);
    }

    /**
     * Test that appointments can be retrieved as calendar events.
     *
     * @return void
     */
    public function test_can_get_appointments_as_calendar_events(): void
    {
        $doctorUser = User::factory()->create(['role' => 'doctor']);
        $doctorUser->assignRole('doctor');
        $doctor = Doctor::factory()->create(['user_id' => $doctorUser->id]);

        $patient = User::factory()->create(['role' => 'patient']);
        $patient->assignRole('patient');

        \App\Models\Appointment::factory()->count(3)->create([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'scheduled_at' => now()->addDay(),
        ]);

        $startDate = now()->format('Y-m-d');
        $endDate = now()->addWeek()->format('Y-m-d');

        $response = $this->actingAs($doctorUser)->getJson("/api/calendar/appointments?start={$startDate}&end={$endDate}");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /**
     * Test that validation fails when creating event without required fields.
     *
     * @return void
     */
    public function test_validation_fails_for_invalid_calendar_event_data(): void
    {
        $doctorUser = User::factory()->create(['role' => 'doctor']);
        $doctorUser->assignRole('doctor');

        $response = $this->actingAs($doctorUser)->postJson('/api/calendar', [
            'title' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'start_time', 'end_time', 'event_type']);
    }

    /**
     * Test that end_time must be after start_time.
     *
     * @return void
     */
    public function test_end_time_must_be_after_start_time(): void
    {
        $doctorUser = User::factory()->create(['role' => 'doctor']);
        $doctorUser->assignRole('doctor');
        $doctor = Doctor::factory()->create(['user_id' => $doctorUser->id]);

        $startTime = now()->format('Y-m-d H:i:s');
        $endTime = now()->subHour()->format('Y-m-d H:i:s');

        $response = $this->actingAs($doctorUser)->postJson('/api/calendar', [
            'title' => 'Test Event',
            'start_time' => $startTime,
            'end_time' => $endTime,
            'event_type' => 'meeting',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['end_time']);
    }
}
