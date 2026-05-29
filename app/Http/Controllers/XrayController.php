<?php

namespace App\Http\Controllers;

use App\Http\Requests\Xray\StoreXrayRequest;
use App\Http\Requests\Xray\UpdateXrayRequest;
use App\Http\Resources\XrayImageResource;
use App\Repositories\XrayImageRepository;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class XrayController extends Controller
{
    use ApiResponse;

    /**
     * XrayController constructor.
     *
     * @param XrayImageRepository $repository The X-ray image repository instance.
     */
    public function __construct(
        private XrayImageRepository $repository
    ) {}

    /**
     * Display a listing of X-ray images for a patient.
     *
     * @param Request $request The request containing filters.
     * @return JsonResponse List of X-ray images.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'patient_id' => 'required|integer|exists:users,id',
            'image_type' => 'nullable|string',
        ]);

        $xrayImages = $this->repository->getByPatient(
            (int) $request->patient_id,
            $request->image_type
        );

        return $this->successResponse(
            XrayImageResource::collection($xrayImages),
            'X-ray images retrieved successfully'
        );
    }

    /**
     * Store a newly created X-ray image.
     *
     * @param StoreXrayRequest $request The validated store request.
     * @return JsonResponse The created X-ray image.
     */
    public function store(StoreXrayRequest $request): JsonResponse
    {
        try {
            $xray = $this->repository->createWithImage(
                $request->validated(),
                $request->file('image')
            );

            return $this->createdResponse(
                new XrayImageResource($xray),
                'X-ray image created successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to create X-ray image: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Display the specified X-ray image.
     *
     * @param int|string $id The X-ray image ID.
     * @return JsonResponse The X-ray image details.
     */
    public function show(int|string $id): JsonResponse
    {
        $xray = $this->repository->findWithRelations((int) $id);

        if (!$xray) {
            return $this->notFoundResponse('X-ray image');
        }

        return $this->successResponse(
            new XrayImageResource($xray),
            'X-ray image retrieved successfully'
        );
    }

    /**
     * Update the specified X-ray image.
     *
     * @param UpdateXrayRequest $request The validated update request.
     * @param int|string $id The X-ray image ID.
     * @return JsonResponse The updated X-ray image.
     */
    public function update(UpdateXrayRequest $request, int|string $id): JsonResponse
    {
        try {
            $xray = $this->repository->updateWithImage(
                (int) $id,
                $request->validated(),
                $request->file('image')
            );

            return $this->successResponse(
                new XrayImageResource($xray),
                'X-ray image updated successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to update X-ray image: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Remove the specified X-ray image.
     *
     * @param int|string $id The X-ray image ID.
     * @return JsonResponse No content response on success.
     */
    public function destroy(int|string $id): JsonResponse
    {
        try {
            $this->repository->deleteWithFiles((int) $id);
            return $this->noContentResponse();
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to delete X-ray image: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Get available image types.
     *
     * @return JsonResponse List of image types.
     */
    public function imageTypes(): JsonResponse
    {
        $types = $this->repository->getImageTypes();

        return $this->successResponse($types, 'Available image types');
    }

    /**
     * Compare two X-ray images.
     *
     * @param int|string $id1 The first X-ray image ID.
     * @param int|string $id2 The second X-ray image ID.
     * @return JsonResponse Comparison data.
     */
    public function compare(int|string $id1, int|string $id2): JsonResponse
    {
        $result = $this->repository->compare((int) $id1, (int) $id2);

        if (!$result) {
            return $this->notFoundResponse('One or both X-ray images');
        }

        return $this->successResponse([
            'xray1' => new XrayImageResource($result['xray1']),
            'xray2' => new XrayImageResource($result['xray2']),
        ], 'Comparison data retrieved successfully');
    }
}
