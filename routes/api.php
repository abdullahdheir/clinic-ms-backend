<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClinicController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\DoctorShiftController;
use App\Http\Controllers\MedicalFileController;
use App\Http\Controllers\MedicalRecordController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\VisitController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
        Route::put('profile', [AuthController::class, 'updateProfile']);
    });

    Route::apiResource('clinics', ClinicController::class)->middleware('role:manager|super_admin');
    Route::apiResource('departments', DepartmentController::class);
    Route::apiResource('doctors', DoctorController::class);
    Route::apiResource('doctor-shifts', DoctorShiftController::class);
    Route::apiResource('patients', PatientController::class);

    Route::get('appointments/available-slots', [AppointmentController::class, 'availableSlots']);
    Route::get('appointments/today', [AppointmentController::class, 'today']);
    Route::patch('appointments/{appointment}/status', [AppointmentController::class, 'updateStatus']);
    Route::apiResource('appointments', AppointmentController::class);

    Route::apiResource('medical-records', MedicalRecordController::class);
    Route::apiResource('visits', VisitController::class);
    Route::apiResource('medical-files', MedicalFileController::class);
    Route::apiResource('notifications', NotificationController::class);
});
