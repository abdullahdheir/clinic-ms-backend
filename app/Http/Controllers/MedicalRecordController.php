<?php

namespace App\Http\Controllers;

use App\Models\MedicalRecord;
use Illuminate\Http\Request;

class MedicalRecordController extends Controller
{
    /**
     * Get all medical records with patient and doctor details
     *
     * @return \Illuminate\Http\JsonResponse - List of medical records
     */
    public function index()
    {
        $records = MedicalRecord::with(['patient', 'doctor.user'])->get();
        return response()->json($records);
    }

    /**
     * Create a new medical record
     *
     * @param Request $request - Medical record data (patient_id, doctor_id, allergies, etc.)
     * @return \Illuminate\Http\JsonResponse - Created medical record
     */
    public function store(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:users,id',
            'doctor_id' => 'required|exists:doctors,id',
            'allergies' => 'nullable|string',
            'chronic_diseases' => 'nullable|string',
            'medications' => 'nullable|string',
            'family_history' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $record = MedicalRecord::create($request->all());
        return response()->json($record, 201);
    }

    /**
     * Get specific medical record
     *
     * @param string $id - Medical record ID
     * @return \Illuminate\Http\JsonResponse - Medical record details
     */
    public function show(string $id)
    {
        $record = MedicalRecord::with(['patient', 'doctor.user'])->findOrFail($id);
        return response()->json($record);
    }

    /**
     * Update medical record
     *
     * @param Request $request - Updated medical record data
     * @param string $id - Medical record ID
     * @return \Illuminate\Http\JsonResponse - Updated medical record
     */
    public function update(Request $request, string $id)
    {
        $record = MedicalRecord::findOrFail($id);

        $request->validate([
            'patient_id' => 'sometimes|required|exists:users,id',
            'doctor_id' => 'sometimes|required|exists:doctors,id',
            'allergies' => 'nullable|string',
            'chronic_diseases' => 'nullable|string',
            'medications' => 'nullable|string',
            'family_history' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $record->update($request->all());
        return response()->json($record);
    }

    /**
     * Delete a medical record
     *
     * @param string $id - Medical record ID
     * @return \Illuminate\Http\JsonResponse - Success message
     */
    public function destroy(string $id)
    {
        $record = MedicalRecord::findOrFail($id);
        $record->delete();
        return response()->json(['message' => 'Medical record deleted successfully']);
    }
}
