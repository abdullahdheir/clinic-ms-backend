<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClinicController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\DoctorShiftController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::apiResource('clinics', ClinicController::class);
    Route::apiResource('departments', DepartmentController::class);
    Route::apiResource('doctors', DoctorController::class);
    Route::apiResource('doctor-shifts', DoctorShiftController::class);
    Route::apiResource('appointments', AppointmentController::class);
});
