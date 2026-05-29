<?php

namespace App\Http\Controllers;

use App\Http\Requests\Prescription\StorePrescriptionRequest;
use App\Http\Requests\Prescription\UpdatePrescriptionRequest;
use App\Http\Resources\PrescriptionResource;
use App\Repositories\PrescriptionRepository;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PrescriptionController extends Controller
{
    use ApiResponse;

    /**
     * PrescriptionController constructor.
     *
     * @param PrescriptionRepository $repository The prescription repository instance.
     */
    public function __construct(
        private PrescriptionRepository $repository
    ) {}

    /**
     * Display a listing of prescriptions for a patient.
     *
     * @param Request $request The request containing patient_id filter.
     * @return JsonResponse List of prescriptions.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'patient_id' => 'required|integer|exists:users,id',
        ]);

        $prescriptions = $this->repository->getByPatient((int) $request->patient_id);

        return $this->successResponse(
            PrescriptionResource::collection($prescriptions),
            'Prescriptions retrieved successfully'
        );
    }

    /**
     * Store a newly created prescription.
     *
     * @param StorePrescriptionRequest $request The validated store request.
     * @return JsonResponse The created prescription.
     */
    public function store(StorePrescriptionRequest $request): JsonResponse
    {
        try {
            $prescription = $this->repository->createWithMedications($request->validated());

            return $this->createdResponse(
                new PrescriptionResource($prescription),
                'Prescription created successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to create prescription: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Display the specified prescription.
     *
     * @param int|string $id The prescription ID.
     * @return JsonResponse The prescription details.
     */
    public function show(int|string $id): JsonResponse
    {
        $prescription = $this->repository->findWithRelations((int) $id);

        if (!$prescription) {
            return $this->errorResponse('Prescription not found', 404);
        }

        return $this->successResponse(
            new PrescriptionResource($prescription),
            'Prescription retrieved successfully'
        );
    }

    /**
     * Update the specified prescription.
     *
     * @param UpdatePrescriptionRequest $request The validated update request.
     * @param int|string $id The prescription ID.
     * @return JsonResponse The updated prescription.
     */
    public function update(UpdatePrescriptionRequest $request, int|string $id): JsonResponse
    {
        try {
            $prescription = $this->repository->updateWithMedications(
                (int) $id,
                $request->validated()
            );

            return $this->successResponse(
                new PrescriptionResource($prescription),
                'Prescription updated successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to update prescription: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Remove the specified prescription.
     *
     * @param int|string $id The prescription ID.
     * @return JsonResponse No content response on success.
     */
    public function destroy(int|string $id): JsonResponse
    {
        $this->repository->delete((int) $id);

        return $this->noContentResponse();
    }

    /**
     * Print the specified prescription.
     *
     * @param int|string $id The prescription ID.
     * @return JsonResponse The prescription ready for printing.
     */
    public function print(int|string $id): JsonResponse
    {
        $prescription = $this->repository->findWithRelations((int) $id);

        if (!$prescription) {
            return $this->errorResponse('Prescription not found', 404);
        }

        return $this->successResponse(
            new PrescriptionResource($prescription),
            'Prescription ready for printing'
        );
    }
}
