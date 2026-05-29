<?php

namespace App\Http\Controllers;

use App\Http\Requests\MedicalReport\StoreMedicalReportRequest;
use App\Http\Requests\MedicalReport\UpdateMedicalReportRequest;
use App\Http\Resources\MedicalReportResource;
use App\Repositories\MedicalReportRepository;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MedicalReportController extends Controller
{
    use ApiResponse;

    /**
     * MedicalReportController constructor.
     *
     * @param MedicalReportRepository $repository The medical report repository instance.
     */
    public function __construct(
        private MedicalReportRepository $repository
    ) {}

    /**
     * Display a listing of medical reports for a patient.
     *
     * @param Request $request The request containing filters.
     * @return JsonResponse List of medical reports.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'patient_id' => 'required|integer|exists:users,id',
            'report_type' => 'nullable|string',
        ]);

        $reports = $this->repository->getByPatient(
            (int) $request->patient_id,
            $request->report_type
        );

        return $this->successResponse(
            MedicalReportResource::collection($reports),
            'Medical reports retrieved successfully'
        );
    }

    /**
     * Store a newly created medical report.
     *
     * @param StoreMedicalReportRequest $request The validated store request.
     * @return JsonResponse The created medical report.
     */
    public function store(StoreMedicalReportRequest $request): JsonResponse
    {
        try {
            $report = $this->repository->createWithFile(
                $request->validated(),
                $request->file('file')
            );

            return $this->createdResponse(
                new MedicalReportResource($report),
                'Medical report created successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to create report: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Display the specified medical report.
     *
     * @param int|string $id The medical report ID.
     * @return JsonResponse The medical report details.
     */
    public function show(int|string $id): JsonResponse
    {
        $report = $this->repository->findWithRelations((int) $id);

        if (!$report) {
            return $this->notFoundResponse('Medical report');
        }

        return $this->successResponse(
            new MedicalReportResource($report),
            'Medical report retrieved successfully'
        );
    }

    /**
     * Update the specified medical report.
     *
     * @param UpdateMedicalReportRequest $request The validated update request.
     * @param int|string $id The medical report ID.
     * @return JsonResponse The updated medical report.
     */
    public function update(UpdateMedicalReportRequest $request, int|string $id): JsonResponse
    {
        try {
            $report = $this->repository->updateWithFile(
                (int) $id,
                $request->validated(),
                $request->file('file')
            );

            return $this->successResponse(
                new MedicalReportResource($report),
                'Medical report updated successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to update report: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Remove the specified medical report.
     *
     * @param int|string $id The medical report ID.
     * @return JsonResponse No content response on success.
     */
    public function destroy(int|string $id): JsonResponse
    {
        try {
            $this->repository->deleteWithFile((int) $id);
            return $this->noContentResponse();
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to delete report: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Download the medical report file.
     *
     * @param int|string $id The medical report ID.
     * @return \Symfony\Component\HttpFoundation\StreamedResponse|JsonResponse The file download or error response.
     */
    public function download(int|string $id): \Symfony\Component\HttpFoundation\StreamedResponse|JsonResponse
    {
        $report = $this->repository->find((int) $id);

        if (!$report) {
            return $this->notFoundResponse('Medical report');
        }

        if (!$report->file_path || !Storage::disk('public')->exists($report->file_path)) {
            return $this->errorResponse('File not found', 404);
        }

        return Storage::disk('public')->download(
            $report->file_path,
            $report->title . '.' . $report->file_type
        );
    }

    /**
     * Get available report types.
     *
     * @return JsonResponse List of report types.
     */
    public function reportTypes(): JsonResponse
    {
        $types = $this->repository->getReportTypes();

        return $this->successResponse($types, 'Available report types');
    }
}
