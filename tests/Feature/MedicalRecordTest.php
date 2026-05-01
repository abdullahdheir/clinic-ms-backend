<?php

use App\Models\User;
use App\Models\MedicalRecord;
use App\Models\Doctor;
use Illuminate\Support\Facades\Hash;

test('user can create medical record', function () {
    $doctor = Doctor::factory()->create();
    $patient = User::factory()->create(['role' => 'patient']);
    $token = $doctor->user->createToken('test-token')->plainTextToken;

    $response = $this->withToken($token)->postJson('/api/medical-records', [
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'allergies' => 'Penicillin',
        'chronic_diseases' => 'Diabetes',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'data' => ['id', 'patient_id', 'doctor_id', 'allergies', 'chronic_diseases'],
        ]);

    $this->assertDatabaseHas('medical_records', [
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'allergies' => 'Penicillin',
    ]);
});

test('user can get all medical records', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;
    MedicalRecord::factory()->count(3)->create();

    $response = $this->withToken($token)->getJson('/api/medical-records');

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

test('user can get single medical record', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;
    $record = MedicalRecord::factory()->create();

    $response = $this->withToken($token)->getJson("/api/medical-records/{$record->id}");

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => $record->id,
                'allergies' => $record->allergies,
            ],
        ]);
});

test('user can update medical record', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;
    $record = MedicalRecord::factory()->create(['allergies' => 'None']);

    $response = $this->withToken($token)->putJson("/api/medical-records/{$record->id}", [
        'allergies' => 'Peanuts',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => $record->id,
                'allergies' => 'Peanuts',
            ],
        ]);
});

test('user can delete medical record', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;
    $record = MedicalRecord::factory()->create();

    $response = $this->withToken($token)->deleteJson("/api/medical-records/{$record->id}");

    $response->assertStatus(200);

    $this->assertDatabaseMissing('medical_records', [
        'id' => $record->id,
    ]);
});
