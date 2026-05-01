<?php

use App\Models\User;
use App\Models\Doctor;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;

test('user can create doctor', function () {
    $user = User::factory()->create(['role' => 'doctor']);
    $department = Department::factory()->create();
    $admin = User::factory()->create(['role' => 'manager']);
    $token = $admin->createToken('test-token')->plainTextToken;

    $response = $this->withToken($token)->postJson('/api/doctors', [
        'user_id' => $user->id,
        'department_id' => $department->id,
        'specialization' => 'Cardiology',
        'consultation_fee' => 150,
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'data' => ['id', 'user_id', 'department_id', 'specialization', 'consultation_fee'],
        ]);

    $this->assertDatabaseHas('doctors', [
        'user_id' => $user->id,
        'department_id' => $department->id,
        'specialization' => 'Cardiology',
    ]);
});

test('user can get all doctors', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;
    Doctor::factory()->count(3)->create();

    $response = $this->withToken($token)->getJson('/api/doctors');

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

test('user can get single doctor', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;
    $doctor = Doctor::factory()->create();

    $response = $this->withToken($token)->getJson("/api/doctors/{$doctor->id}");

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => $doctor->id,
                'specialization' => $doctor->specialization,
            ],
        ]);
});

test('user can update doctor', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;
    $doctor = Doctor::factory()->create(['specialization' => 'General']);

    $response = $this->withToken($token)->putJson("/api/doctors/{$doctor->id}", [
        'specialization' => 'Cardiology',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => $doctor->id,
                'specialization' => 'Cardiology',
            ],
        ]);
});

test('user can delete doctor', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;
    $doctor = Doctor::factory()->create();

    $response = $this->withToken($token)->deleteJson("/api/doctors/{$doctor->id}");

    $response->assertStatus(200);

    $this->assertDatabaseMissing('doctors', [
        'id' => $doctor->id,
    ]);
});
