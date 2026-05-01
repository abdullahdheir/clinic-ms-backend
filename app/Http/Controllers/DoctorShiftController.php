<?php

namespace App\Http\Controllers;

use App\Models\DoctorShift;
use Illuminate\Http\Request;

class DoctorShiftController extends Controller
{
    /**
     * Get all doctor shifts with doctor details
     *
     * @return \Illuminate\Http\JsonResponse - List of doctor shifts
     */
    public function index()
    {
        $shifts = DoctorShift::with('doctor.user')->get();
        return response()->json($shifts);
    }

    /**
     * Create a new doctor shift
     *
     * @param Request $request - Shift data (doctor_id, day_of_week, start_time, end_time)
     * @return \Illuminate\Http\JsonResponse - Created shift
     */
    public function store(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'day_of_week' => 'required|in:saturday,sunday,monday,tuesday,wednesday,thursday,friday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'is_active' => 'boolean',
        ]);

        $shift = DoctorShift::create($request->all());
        return response()->json($shift, 201);
    }

    /**
     * Get specific doctor shift
     *
     * @param string $id - Shift ID
     * @return \Illuminate\Http\JsonResponse - Shift details
     */
    public function show(string $id)
    {
        $shift = DoctorShift::with('doctor.user')->findOrFail($id);
        return response()->json($shift);
    }

    /**
     * Update doctor shift
     *
     * @param Request $request - Updated shift data
     * @param string $id - Shift ID
     * @return \Illuminate\Http\JsonResponse - Updated shift
     */
    public function update(Request $request, string $id)
    {
        $shift = DoctorShift::findOrFail($id);

        $request->validate([
            'doctor_id' => 'sometimes|required|exists:doctors,id',
            'day_of_week' => 'sometimes|required|in:saturday,sunday,monday,tuesday,wednesday,thursday,friday',
            'start_time' => 'sometimes|required|date_format:H:i',
            'end_time' => 'sometimes|required|date_format:H:i|after:start_time',
            'is_active' => 'boolean',
        ]);

        $shift->update($request->all());
        return response()->json($shift);
    }

    /**
     * Delete a doctor shift
     *
     * @param string $id - Shift ID
     * @return \Illuminate\Http\JsonResponse - Success message
     */
    public function destroy(string $id)
    {
        $shift = DoctorShift::findOrFail($id);
        $shift->delete();
        return response()->json(['message' => 'Doctor shift deleted successfully']);
    }
}
