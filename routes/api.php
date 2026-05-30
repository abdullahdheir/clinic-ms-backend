<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClinicController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\DoctorShiftController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\MedicalFileController;
use App\Http\Controllers\MedicalRecordController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DoctorNoteController;
use App\Http\Controllers\MedicalReportController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\VisitController;
use App\Http\Controllers\XrayController;
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

    Route::apiResource('clinics', ClinicController::class);
    Route::apiResource('departments', DepartmentController::class);
    Route::apiResource('doctors', DoctorController::class);
    Route::apiResource('doctor-shifts', DoctorShiftController::class);
    Route::apiResource('patients', PatientController::class);

    // Clinic Manager specific endpoints
    Route::middleware('role:manager')->prefix('manager')->group(function () {
        Route::get('stats', [ClinicController::class, 'stats']);
        Route::get('clinics/{clinic}/doctors', [ClinicController::class, 'doctors']);
        Route::get('clinics/{clinic}/departments', [ClinicController::class, 'departments']);
        Route::get('clinics/{clinic}/appointments', [ClinicController::class, 'appointments']);
        Route::get('clinics/{clinic}/patients', [ClinicController::class, 'patients']);
        Route::post('clinics/{clinic}/doctors/{doctor}', [ClinicController::class, 'attachDoctor']);
        Route::delete('clinics/{clinic}/doctors/{doctor}', [ClinicController::class, 'detachDoctor']);
        Route::post('clinics/{clinic}/departments/{department}', [ClinicController::class, 'attachDepartment']);
        Route::delete('clinics/{clinic}/departments/{department}', [ClinicController::class, 'detachDepartment']);
    });

    // Doctor specific endpoints
    Route::middleware('role:doctor')->prefix('doctor')->group(function () {
        Route::get('stats', [DoctorController::class, 'stats']);
        Route::get('appointments', [DoctorController::class, 'appointments']);
        Route::get('patients', [DoctorController::class, 'patients']);
        Route::get('clinics', [DoctorController::class, 'clinics']);
        Route::get('departments', [DoctorController::class, 'departments']);
        Route::post('appointments/{appointment}/complete', [DoctorController::class, 'completeAppointment']);
        Route::post('prescriptions', [PrescriptionController::class, 'store']);
        Route::post('medical-reports', [MedicalReportController::class, 'store']);
        Route::post('doctor-notes', [DoctorNoteController::class, 'store']);
    });

    // Receptionist specific endpoints
    Route::middleware('role:receptionist')->prefix('receptionist')->group(function () {
        Route::get('stats', [AppointmentController::class, 'receptionistStats']);
        Route::get('appointments', [AppointmentController::class, 'receptionistAppointments']);
        Route::post('appointments/{appointment}/check-in', [AppointmentController::class, 'checkIn']);
        Route::post('appointments/{appointment}/confirm', [AppointmentController::class, 'confirm']);
        Route::get('patients', [PatientController::class, 'index']);
    });

    // Patient specific endpoints
    Route::middleware('role:patient')->prefix('patient')->group(function () {
        Route::get('stats', [PatientController::class, 'stats']);
        Route::get('appointments', [AppointmentController::class, 'patientAppointments']);
        Route::post('appointments/{appointment}/cancel', [AppointmentController::class, 'cancel']);
        Route::get('medical-records', [MedicalRecordController::class, 'patientRecords']);
        Route::get('prescriptions', [PrescriptionController::class, 'patientPrescriptions']);
        Route::get('medical-reports', [MedicalReportController::class, 'patientReports']);
    });

    Route::get('appointments/available-slots', [AppointmentController::class, 'availableSlots']);
    Route::get('appointments/today', [AppointmentController::class, 'today']);
    Route::patch('appointments/{appointment}/status', [AppointmentController::class, 'updateStatus']);
    Route::apiResource('appointments', AppointmentController::class);

    Route::apiResource('medical-records', MedicalRecordController::class);
    Route::post('visits/{visit}/files', [VisitController::class, 'uploadFiles']);
    Route::apiResource('visits', VisitController::class);
    Route::apiResource('medical-files', MedicalFileController::class);

    Route::post('invoices/{invoice}/pay', [InvoiceController::class, 'markAsPaid']);
    Route::apiResource('invoices', InvoiceController::class);

    // Notification routes (require authentication)
    Route::post('notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::get('notifications/latest', [NotificationController::class, 'latest']);
    Route::apiResource('notifications', NotificationController::class);

    // Reports routes
    Route::get('reports/dashboard', [ReportController::class, 'dashboard']);
    Route::get('reports/appointments', [ReportController::class, 'appointments']);
    Route::get('reports/revenue', [ReportController::class, 'revenue']);
    Route::get('reports/patients', [ReportController::class, 'patients']);
    Route::get('reports/doctors', [ReportController::class, 'doctors']);

    // Coming Soon Features - NOW IMPLEMENTED ✅

    // Prescriptions routes (الوصفات الطبية)
    Route::get('prescriptions/types', [PrescriptionController::class, 'types']);
    Route::get('prescriptions/{id}/print', [PrescriptionController::class, 'print']);
    Route::apiResource('prescriptions', PrescriptionController::class);

    // Medical Reports routes (التقارير الطبية)
    Route::get('medical-reports/types', [MedicalReportController::class, 'reportTypes']);
    Route::get('medical-reports/{id}/download', [MedicalReportController::class, 'download']);
    Route::apiResource('medical-reports', MedicalReportController::class);

    // X-ray Images routes (صور الأشعة)
    Route::get('xray-images/types', [XrayController::class, 'imageTypes']);
    Route::get('xray-images/compare/{id1}/{id2}', [XrayController::class, 'compare']);
    Route::apiResource('xray-images', XrayController::class);

    // Doctor Notes routes (ملاحظات الطبيب)
    Route::post('doctor-notes/{id}/toggle-pin', [DoctorNoteController::class, 'togglePin']);
    Route::apiResource('doctor-notes', DoctorNoteController::class);

    // Calendar routes (التقويم)
    Route::get('calendar/appointments', [CalendarController::class, 'appointments']);
    Route::patch('calendar/appointments/{id}/time', [CalendarController::class, 'updateAppointmentTime']);
    Route::apiResource('calendar', CalendarController::class);
});
