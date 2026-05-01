<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use Illuminate\Http\Request;

class VisitController extends Controller
{
    /**
     * Get all visits with patient, doctor, and appointment details
     *
     * @return \Illuminate\Http\JsonResponse - List of visits
     */
    public function index()
    {
        $visits = Visit::with(['patient', 'doctor.user', 'appointment'])->get();
        return response()->json($visits);
    }

    /**
     * Create a new visit
     *
     * @param Request $request - Visit data (patient_id, doctor_id, visit_date, diagnosis, etc.)
     * @return \Illuminate\Http\JsonResponse - Created visit
     */
    public function store(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:users,id',
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'visit_date' => 'required|date',
            'chief_complaint' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'prescription' => 'nullable|string',
            'notes' => 'nullable|string',
            'visit_type' => 'required|in:consultation,follow_up,emergency',
        ]);

        $visit = Visit::create($request->all());
        return response()->json($visit, 201);
    }

    /**
     * Get specific visit
     *
     * @param string $id - Visit ID
     * @return \Illuminate\Http\JsonResponse - Visit details
     */
    public function show(string $id)
    {
        $visit = Visit::with(['patient', 'doctor.user', 'appointment', 'medicalFiles'])->findOrFail($id);
        return response()->json($visit);
    }

    /**
     * Update visit
     *
     * @param Request $request - Updated visit data
     * @param string $id - Visit ID
     * @return \Illuminate\Http\JsonResponse - Updated visit
     */
    public function update(Request $request, string $id)
    {
        $visit = Visit::findOrFail($id);

        $request->validate([
            'patient_id' => 'sometimes|required|exists:users,id',
            'doctor_id' => 'sometimes|required|exists:doctors,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'visit_date' => 'sometimes|required|date',
            'chief_complaint' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'prescription' => 'nullable|string',
            'notes' => 'nullable|string',
            'visit_type' => 'sometimes|required|in:consultation,follow_up,emergency',
        ]);

        $visit->update($request->all());
        return response()->json($visit);
    }

    /**
     * Delete a visit
     *
     * @param string $id - Visit ID
     * @return \Illuminate\Http\JsonResponse - Success message
     */
    public function destroy(string $id)
    {
        $visit = Visit::findOrFail($id);
        $visit->delete();
        return response()->json(['message' => 'Visit deleted successfully']);
    }
}
