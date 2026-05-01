<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    /**
     * Get all departments with clinic and doctors
     *
     * @return \Illuminate\Http\JsonResponse - List of departments
     */
    public function index()
    {
        $departments = Department::with(['clinic', 'doctors'])->get();
        return response()->json($departments);
    }

    /**
     * Create a new department
     *
     * @param Request $request - Department data (clinic_id, name, specialty, etc.)
     * @return \Illuminate\Http\JsonResponse - Created department
     */
    public function store(Request $request)
    {
        $request->validate([
            'clinic_id' => 'required|exists:clinics,id',
            'name' => 'required|string|max:255',
            'specialty' => 'nullable|string|max:255',
            'max_capacity' => 'integer|min:1',
            'description' => 'nullable|string',
        ]);

        $department = Department::create($request->all());
        return response()->json($department, 201);
    }

    /**
     * Get specific department with clinic and doctors
     *
     * @param string $id - Department ID
     * @return \Illuminate\Http\JsonResponse - Department details
     */
    public function show(string $id)
    {
        $department = Department::with(['clinic', 'doctors.user'])->findOrFail($id);
        return response()->json($department);
    }

    /**
     * Update department details
     *
     * @param Request $request - Updated department data
     * @param string $id - Department ID
     * @return \Illuminate\Http\JsonResponse - Updated department
     */
    public function update(Request $request, string $id)
    {
        $department = Department::findOrFail($id);

        $request->validate([
            'clinic_id' => 'sometimes|required|exists:clinics,id',
            'name' => 'sometimes|required|string|max:255',
            'specialty' => 'nullable|string|max:255',
            'max_capacity' => 'integer|min:1',
            'description' => 'nullable|string',
        ]);

        $department->update($request->all());
        return response()->json($department);
    }

    /**
     * Delete a department
     *
     * @param string $id - Department ID
     * @return \Illuminate\Http\JsonResponse - Success message
     */
    public function destroy(string $id)
    {
        $department = Department::findOrFail($id);
        $department->delete();
        return response()->json(['message' => 'Department deleted successfully']);
    }
}
