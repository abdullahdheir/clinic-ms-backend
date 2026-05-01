<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Models\Department;
use App\Traits\ApiResponse;

class DepartmentController extends Controller
{
    use ApiResponse;

    /**
     * Get all departments with clinic and doctors
     *
     * @return \Illuminate\Http\JsonResponse - List of departments
     */
    public function index()
    {
        $departments = Department::with(['clinic', 'doctors'])->get();
        return $this->successResponse($departments);
    }

    /**
     * Create a new department
     *
     * @param StoreDepartmentRequest $request - Validated department data
     * @return \Illuminate\Http\JsonResponse - Created department
     */
    public function store(StoreDepartmentRequest $request)
    {
        $department = Department::create($request->only([
            'clinic_id',
            'name',
            'specialty',
            'max_capacity',
            'description',
        ]));
        return $this->createdResponse($department);
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
        return $this->successResponse($department);
    }

    /**
     * Update department details
     *
     * @param UpdateDepartmentRequest $request - Validated department data
     * @param string $id - Department ID
     * @return \Illuminate\Http\JsonResponse - Updated department
     */
    public function update(UpdateDepartmentRequest $request, string $id)
    {
        $department = Department::findOrFail($id);
        $department->update($request->only([
            'clinic_id',
            'name',
            'specialty',
            'max_capacity',
            'description',
        ]));
        return $this->successResponse($department);
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
        return $this->successResponse(null, 'Department deleted successfully');
    }
}
