<?php

namespace App\Http\Controllers;

use App\Http\Requests\Clinic\StoreClinicRequest;
use App\Http\Requests\Clinic\UpdateClinicRequest;
use App\Repositories\ClinicRepository;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class ClinicController extends Controller
{
    use ApiResponse;

    /**
     * ClinicController constructor.
     * 
     * @param ClinicRepository $repository The clinic repository instance.
     */
    public function __construct(
        private ClinicRepository $repository
    ) {}

    /**
     * Display a listing of the clinics.
     * 
     * @return JsonResponse List of clinics with manager details.
     */
    public function index(): JsonResponse
    {
        $clinics = $this->repository->allWithRelations();
        return $this->successResponse($clinics);
    }

    /**
     * Store a newly created clinic in storage.
     * 
     * @param StoreClinicRequest $request The validated request containing clinic data.
     * @return JsonResponse The created clinic.
     */
    public function store(StoreClinicRequest $request): JsonResponse
    {
        $clinic = $this->repository->create($request->validated());
        return $this->createdResponse($clinic);
    }

    /**
     * Display the specified clinic.
     * 
     * @param int|string $id The clinic ID.
     * @return JsonResponse The clinic with its manager and departments.
     */
    public function show(int|string $id): JsonResponse
    {
        $clinic = $this->repository->findWithRelationsOrFail($id);
        return $this->successResponse($clinic);
    }

    /**
     * Update the specified clinic in storage.
     * 
     * @param UpdateClinicRequest $request The validated request containing updated data.
     * @param int|string $id The clinic ID.
     * @return JsonResponse The updated clinic.
     */
    public function update(UpdateClinicRequest $request, int|string $id): JsonResponse
    {
        $clinic = $this->repository->update($id, $request->validated());
        return $this->successResponse($clinic);
    }

    /**
     * Remove the specified clinic from storage.
     * 
     * @param int|string $id The clinic ID.
     * @return JsonResponse No content response on success.
     */
    public function destroy(int|string $id): JsonResponse
    {
        $this->repository->delete($id);
        return $this->noContentResponse();
    }
}
