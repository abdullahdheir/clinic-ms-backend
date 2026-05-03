<?php

namespace App\Http\Controllers;

use App\Http\Requests\Department\StoreDepartmentRequest;
use App\Http\Requests\Department\UpdateDepartmentRequest;
use App\Repositories\DepartmentRepository;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class DepartmentController extends Controller
{
    use ApiResponse;

    /**
     * DepartmentController constructor.
     * 
     * @param DepartmentRepository $repository The department repository instance.
     */
    public function __construct(
        private DepartmentRepository $repository
    ) {}

    /**
     * Display a listing of the departments.
     * 
     * @return JsonResponse List of departments with clinic and doctors.
     */
    public function index(): JsonResponse
    {
        $departments = $this->repository->allWithRelations();
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
        $department = $this->repository->create($request->only([
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
        $department = $this->repository->findWithRelationsOrFail($id);
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
        $department = $this->repository->update($id, $request->only([
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
        $this->repository->delete($id);
        return $this->successResponse(null, 'Department deleted successfully');
    }
}
