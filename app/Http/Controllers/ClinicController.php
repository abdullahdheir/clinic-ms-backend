<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClinicRequest;
use App\Http\Requests\UpdateClinicRequest;
use App\Models\Clinic;
use App\Traits\ApiResponse;

class ClinicController extends Controller
{
    use ApiResponse;

    /**
     * Get all clinics with manager details
     *
     * @return \Illuminate\Http\JsonResponse - List of clinics
     */
    public function index()
    {
        $clinics = Clinic::with('manager')->get();
        return $this->successResponse($clinics);
    }

    /**
     * Create a new clinic
     *
     * @param StoreClinicRequest $request - Validated clinic data
     * @return \Illuminate\Http\JsonResponse - Created clinic
     */
    public function store(StoreClinicRequest $request)
    {
        $clinic = Clinic::create($request->only([
            'name',
            'address',
            'phone',
            'logo_url',
            'manager_id',
            'working_hours',
            'is_active',
        ]));
        return $this->createdResponse($clinic);
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
        return $this->successResponse($clinic);
    }

    /**
     * Update clinic details
     *
     * @param UpdateClinicRequest $request - Validated clinic data
     * @param string $id - Clinic ID
     * @return \Illuminate\Http\JsonResponse - Updated clinic
     */
    public function update(UpdateClinicRequest $request, string $id)
    {
        $clinic = Clinic::findOrFail($id);
        $clinic->update($request->only([
            'name',
            'address',
            'phone',
            'logo_url',
            'manager_id',
            'working_hours',
            'is_active',
        ]));
        return $this->successResponse($clinic);
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
        return $this->successResponse(null, 'Clinic deleted successfully');
    }
}
