<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use Illuminate\Http\Request;

class ClinicController extends Controller
{
    /**
     * Get all clinics with manager details
     *
     * @return \Illuminate\Http\JsonResponse - List of clinics
     */
    public function index()
    {
        $clinics = Clinic::with('manager')->get();
        return response()->json($clinics);
    }

    /**
     * Create a new clinic
     *
     * @param Request $request - Clinic data (name, address, phone, manager_id, etc.)
     * @return \Illuminate\Http\JsonResponse - Created clinic
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'logo_url' => 'nullable|url',
            'manager_id' => 'nullable|exists:users,id',
            'working_hours' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $clinic = Clinic::create($request->all());
        return response()->json($clinic, 201);
    }

    /**
     * Get specific clinic with manager and departments
     *
     * @param string $id - Clinic ID
     * @return \Illuminate\Http\JsonResponse - Clinic details
     */
    public function show(string $id)
    {
        $clinic = Clinic::with(['manager', 'departments'])->findOrFail($id);
        return response()->json($clinic);
    }

    /**
     * Update clinic details
     *
     * @param Request $request - Updated clinic data
     * @param string $id - Clinic ID
     * @return \Illuminate\Http\JsonResponse - Updated clinic
     */
    public function update(Request $request, string $id)
    {
        $clinic = Clinic::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'logo_url' => 'nullable|url',
            'manager_id' => 'nullable|exists:users,id',
            'working_hours' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $clinic->update($request->all());
        return response()->json($clinic);
    }

    /**
     * Delete a clinic
     *
     * @param string $id - Clinic ID
     * @return \Illuminate\Http\JsonResponse - Success message
     */
    public function destroy(string $id)
    {
        $clinic = Clinic::findOrFail($id);
        $clinic->delete();
        return response()->json(['message' => 'Clinic deleted successfully']);
    }
}
