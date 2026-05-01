<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    /**
     * Get all appointments with related data
     *
     * @return \Illuminate\Http\JsonResponse - List of appointments
     */
    public function index()
    {
        $appointments = Appointment::with(['patient', 'doctor.user', 'department'])->get();
        return response()->json($appointments);
    }

    /**
     * Create a new appointment
     *
     * @param Request $request - Appointment data (patient_id, doctor_id, appointment_date, reason)
     * @return \Illuminate\Http\JsonResponse - Created appointment
     */
    public function store(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:users,id',
            'doctor_id' => 'required|exists:doctors,id',
            'department_id' => 'nullable|exists:departments,id',
            'appointment_date' => 'required|date|after:now',
            'reason' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $appointment = Appointment::create($request->all());
        return response()->json($appointment, 201);
    }

    /**
     * Get specific appointment
     *
     * @param string $id - Appointment ID
     * @return \Illuminate\Http\JsonResponse - Appointment details
     */
    public function show(string $id)
    {
        $appointment = Appointment::with(['patient', 'doctor.user', 'department', 'checkedInBy'])->findOrFail($id);
        return response()->json($appointment);
    }

    /**
     * Update appointment status or details
     *
     * @param Request $request - Updated appointment data
     * @param string $id - Appointment ID
     * @return \Illuminate\Http\JsonResponse - Updated appointment
     */
    public function update(Request $request, string $id)
    {
        $appointment = Appointment::findOrFail($id);

        $request->validate([
            'patient_id' => 'sometimes|required|exists:users,id',
            'doctor_id' => 'sometimes|required|exists:doctors,id',
            'department_id' => 'nullable|exists:departments,id',
            'appointment_date' => 'sometimes|required|date',
            'status' => 'sometimes|required|in:pending,confirmed,completed,cancelled,no_show',
            'reason' => 'nullable|string',
            'notes' => 'nullable|string',
            'checked_in_at' => 'nullable|date',
            'checked_in_by' => 'nullable|exists:users,id',
        ]);

        $appointment->update($request->all());
        return response()->json($appointment);
    }

    /**
     * Delete an appointment
     *
     * @param string $id - Appointment ID
     * @return \Illuminate\Http\JsonResponse - Success message
     */
    public function destroy(string $id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->delete();
        return response()->json(['message' => 'Appointment deleted successfully']);
    }
}
