<?php

use App\Models\User;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;

test('user can create appointment', function () {
    $patient = User::factory()->create(['role' => 'patient']);
    $department = Department::factory()->create();
    $doctor = Doctor::factory()->create(['department_id' => $department->id]);
    $token = $patient->createToken('test-token')->plainTextToken;

    $response = $this->withToken($token)->postJson('/api/appointments', [
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'department_id' => $department->id,
        'appointment_date' => now()->addDay()->toDateTimeString(),
        'reason' => 'Regular checkup',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'data' => ['id', 'patient_id', 'doctor_id', 'department_id', 'appointment_date', 'reason'],
        ]);

    $this->assertDatabaseHas('appointments', [
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'reason' => 'Regular checkup',
    ]);
});

test('user can get all appointments', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;
    Appointment::factory()->count(3)->create();

    $response = $this->withToken($token)->getJson('/api/appointments');

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

test('user can get single appointment', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;
    $appointment = Appointment::factory()->create();

    $response = $this->withToken($token)->getJson("/api/appointments/{$appointment->id}");

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => $appointment->id,
                'reason' => $appointment->reason,
            ],
        ]);
});

test('user can update appointment', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;
    $appointment = Appointment::factory()->create(['status' => 'scheduled']);

    $response = $this->withToken($token)->putJson("/api/appointments/{$appointment->id}", [
        'status' => 'confirmed',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => $appointment->id,
                'status' => 'confirmed',
            ],
        ]);
});

test('user can delete appointment', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;
    $appointment = Appointment::factory()->create();

    $response = $this->withToken($token)->deleteJson("/api/appointments/{$appointment->id}");

    $response->assertStatus(200);

    $this->assertDatabaseMissing('appointments', [
        'id' => $appointment->id,
    ]);
});
