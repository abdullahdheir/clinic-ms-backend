<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMedicalFileRequest;
use App\Http\Requests\UpdateMedicalFileRequest;
use App\Models\MedicalFile;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Storage;

class MedicalFileController extends Controller
{
    use ApiResponse;

    /**
     * Get all medical files with patient and visit details
     *
     * @return \Illuminate\Http\JsonResponse - List of medical files
     */
    public function index()
    {
        $files = MedicalFile::with(['patient', 'visit'])->get();
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
        
        $file = MedicalFile::create([
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
        $file = MedicalFile::with(['patient', 'visit'])->findOrFail($id);
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
        $file = MedicalFile::findOrFail($id);
        $file->update($request->only('description'));
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
        $file = MedicalFile::findOrFail($id);
        
        if (Storage::disk('public')->exists($file->file_path)) {
            Storage::disk('public')->delete($file->file_path);
        }
        
        $file->delete();
        return $this->successResponse(null, 'Medical file deleted successfully');
    }
}
