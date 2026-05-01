<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMedicalFileRequest;
use App\Http\Requests\UpdateMedicalFileRequest;
use App\Repositories\MedicalFileRepository;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Storage;

class MedicalFileController extends Controller
{
    use ApiResponse;

    public function __construct(
        private MedicalFileRepository $repository
    ) {}

    /**
     * Get all medical files with patient and visit details
     *
     * @return \Illuminate\Http\JsonResponse - List of medical files
     */
    public function index()
    {
        $files = $this->repository->allWithRelations();
        return $this->successResponse($files);
    }

    /**
     * Upload a new medical file
     *
     * @param StoreMedicalFileRequest $request - Validated file data
     * @return \Illuminate\Http\JsonResponse - Created medical file
     */
    public function store(StoreMedicalFileRequest $request)
    {
        $path = $request->file('file')->store('medical-files', 'public');
        
        $file = $this->repository->create([
            'patient_id' => $request->patient_id,
            'visit_id' => $request->visit_id,
            'file_name' => $request->file('file')->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $request->file('file')->getClientMimeType(),
            'file_size' => $request->file('file')->getSize(),
            'description' => $request->description,
        ]);

        return $this->createdResponse($file);
    }

    /**
     * Get specific medical file
     *
     * @param string $id - Medical file ID
     * @return \Illuminate\Http\JsonResponse - Medical file details
     */
    public function show(string $id)
    {
        $file = $this->repository->findWithRelationsOrFail($id);
        return $this->successResponse($file);
    }

    /**
     * Update medical file description
     *
     * @param UpdateMedicalFileRequest $request - Validated description
     * @param string $id - Medical file ID
     * @return \Illuminate\Http\JsonResponse - Updated medical file
     */
    public function update(UpdateMedicalFileRequest $request, string $id)
    {
        $file = $this->repository->update($id, $request->only('description'));
        return $this->successResponse($file);
    }

    /**
     * Delete a medical file
     *
     * @param string $id - Medical file ID
     * @return \Illuminate\Http\JsonResponse - Success message
     */
    public function destroy(string $id)
    {
        $this->repository->deleteWithFile($id);
        return $this->successResponse(null, 'Medical file deleted successfully');
    }
}
