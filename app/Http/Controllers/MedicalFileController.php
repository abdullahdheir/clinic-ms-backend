<?php

namespace App\Http\Controllers;

use App\Models\MedicalFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MedicalFileController extends Controller
{
    /**
     * Get all medical files with patient and visit details
     *
     * @return \Illuminate\Http\JsonResponse - List of medical files
     */
    public function index()
    {
        $files = MedicalFile::with(['patient', 'visit'])->get();
        return response()->json($files);
    }

    /**
     * Upload a new medical file
     *
     * @param Request $request - File data (patient_id, file, description)
     * @return \Illuminate\Http\JsonResponse - Created medical file
     */
    public function store(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:users,id',
            'visit_id' => 'nullable|exists:visits,id',
            'file' => 'required|file|max:10240',
            'description' => 'nullable|string',
        ]);

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

        return response()->json($file, 201);
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
        return response()->json($file);
    }

    /**
     * Update medical file description
     *
     * @param Request $request - Updated description
     * @param string $id - Medical file ID
     * @return \Illuminate\Http\JsonResponse - Updated medical file
     */
    public function update(Request $request, string $id)
    {
        $file = MedicalFile::findOrFail($id);

        $request->validate([
            'description' => 'nullable|string',
        ]);

        $file->update($request->only('description'));
        return response()->json($file);
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
        return response()->json(['message' => 'Medical file deleted successfully']);
    }
}
