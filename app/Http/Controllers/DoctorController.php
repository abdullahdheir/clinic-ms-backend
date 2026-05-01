<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    /**
     * Get all doctors with user and department details
     *
     * @return \Illuminate\Http\JsonResponse - List of doctors
     */
    public function index()
    {
        $doctors = Doctor::with(['user', 'department'])->get();
        return response()->json($doctors);
    }

    /**
     * Create a new doctor profile
     *
     * @param Request $request - Doctor data (user_id, department_id, bio, etc.)
     * @return \Illuminate\Http\JsonResponse - Created doctor
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'department_id' => 'nullable|exists:departments,id',
            'bio' => 'nullable|string',
            'specialization' => 'nullable|string|max:255',
            'session_duration_minutes' => 'integer|min:5',
            'consultation_fee' => 'numeric|min:0',
        ]);

        $doctor = Doctor::create($request->all());
        return response()->json($doctor, 201);
    }

    /**
     * Get specific doctor with user and department
     *
     * @param string $id - Doctor ID
     * @return \Illuminate\Http\JsonResponse - Doctor details
     */
    public function show(string $id)
    {
        $doctor = Doctor::with(['user', 'department.clinic'])->findOrFail($id);
        return response()->json($doctor);
    }

    /**
     * Update doctor details
     *
     * @param Request $request - Updated doctor data
     * @param string $id - Doctor ID
     * @return \Illuminate\Http\JsonResponse - Updated doctor
     */
    public function update(Request $request, string $id)
    {
        $doctor = Doctor::findOrFail($id);

        $request->validate([
            'user_id' => 'sometimes|required|exists:users,id',
            'department_id' => 'nullable|exists:departments,id',
            'bio' => 'nullable|string',
            'specialization' => 'nullable|string|max:255',
            'session_duration_minutes' => 'integer|min:5',
            'consultation_fee' => 'numeric|min:0',
        ]);

        $doctor->update($request->all());
        return response()->json($doctor);
    }

    /**
     * Delete a doctor
     *
     * @param string $id - Doctor ID
     * @return \Illuminate\Http\JsonResponse - Success message
     */
    public function destroy(string $id)
    {
        $doctor = Doctor::findOrFail($id);
        $doctor->delete();
        return response()->json(['message' => 'Doctor deleted successfully']);
    }
}
