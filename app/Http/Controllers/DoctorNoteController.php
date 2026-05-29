<?php

namespace App\Http\Controllers;

use App\Http\Requests\DoctorNote\StoreDoctorNoteRequest;
use App\Http\Requests\DoctorNote\UpdateDoctorNoteRequest;
use App\Http\Resources\DoctorNoteResource;
use App\Repositories\DoctorNoteRepository;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DoctorNoteController extends Controller
{
    use ApiResponse;

    /**
     * DoctorNoteController constructor.
     *
     * @param DoctorNoteRepository $repository The doctor note repository instance.
     */
    public function __construct(
        private DoctorNoteRepository $repository
    ) {}

    /**
     * Display a listing of doctor notes for a patient.
     *
     * @param Request $request The request containing filters.
     * @return JsonResponse List of doctor notes.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'patient_id' => 'required|integer|exists:users,id',
            'note_type' => 'nullable|in:general,follow_up,urgent,routine',
        ]);

        $notes = $this->repository->getByPatient(
            (int) $request->patient_id,
            $request->note_type
        );

        return $this->successResponse(
            DoctorNoteResource::collection($notes),
            'Doctor notes retrieved successfully'
        );
    }

    /**
     * Store a newly created doctor note.
     *
     * @param StoreDoctorNoteRequest $request The validated store request.
     * @return JsonResponse The created doctor note.
     */
    public function store(StoreDoctorNoteRequest $request): JsonResponse
    {
        try {
            $note = $this->repository->createNote($request->validated());

            return $this->createdResponse(
                new DoctorNoteResource($note),
                'Doctor note created successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to create note: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Display the specified doctor note.
     *
     * @param int|string $id The doctor note ID.
     * @return JsonResponse The doctor note details.
     */
    public function show(int|string $id): JsonResponse
    {
        $note = $this->repository->findWithRelations((int) $id);

        if (!$note) {
            return $this->notFoundResponse('Doctor note');
        }

        return $this->successResponse(
            new DoctorNoteResource($note),
            'Doctor note retrieved successfully'
        );
    }

    /**
     * Update the specified doctor note.
     *
     * @param UpdateDoctorNoteRequest $request The validated update request.
     * @param int|string $id The doctor note ID.
     * @return JsonResponse The updated doctor note.
     */
    public function update(UpdateDoctorNoteRequest $request, int|string $id): JsonResponse
    {
        try {
            $note = $this->repository->update((int) $id, $request->validated());

            return $this->successResponse(
                new DoctorNoteResource($note),
                'Doctor note updated successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to update note: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Remove the specified doctor note.
     *
     * @param int|string $id The doctor note ID.
     * @return JsonResponse No content response on success.
     */
    public function destroy(int|string $id): JsonResponse
    {
        $this->repository->delete((int) $id);

        return $this->noContentResponse();
    }

    /**
     * Toggle pin status of the specified doctor note.
     *
     * @param int|string $id The doctor note ID.
     * @return JsonResponse The updated doctor note with new pin status.
     */
    public function togglePin(int|string $id): JsonResponse
    {
        $note = $this->repository->togglePin((int) $id);

        if (!$note) {
            return $this->notFoundResponse('Doctor note');
        }

        $message = $note->is_pinned
            ? 'Doctor note pinned successfully'
            : 'Doctor note unpinned successfully';

        return $this->successResponse(
            new DoctorNoteResource($note),
            $message
        );
    }
}
