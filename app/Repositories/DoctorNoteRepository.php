<?php

namespace App\Repositories;

use App\Models\DoctorNote;
use Illuminate\Database\Eloquent\Collection;

class DoctorNoteRepository extends BaseRepository
{
    /**
     * DoctorNoteRepository constructor.
     *
     * @param DoctorNote $model The doctor note model instance.
     */
    public function __construct(DoctorNote $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all notes for a patient with optional type filter.
     *
     * @param int $patientId The patient ID.
     * @param string|null $type The note type filter.
     * @return Collection List of doctor notes.
     */
    public function getByPatient(int $patientId, ?string $type = null): Collection
    {
        $query = $this->model
            ->with(['doctor.user'])
            ->where('patient_id', $patientId);

        if ($type) {
            $query->where('note_type', $type);
        }

        return $query
            ->orderBy('is_pinned', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Find note by ID with all relationships.
     *
     * @param int $id The note ID.
     * @return DoctorNote|null
     */
    public function findWithRelations(int $id): ?DoctorNote
    {
        return $this->model
            ->with(['doctor.user', 'patient.user'])
            ->find($id);
    }

    /**
     * Create a new doctor note.
     *
     * @param array $data The note data.
     * @return DoctorNote The created note.
     */
    public function createNote(array $data): DoctorNote
    {
        $data['is_pinned'] = $data['is_pinned'] ?? false;

        return $this->create($data);
    }

    /**
     * Toggle pin status of a note.
     *
     * @param int $id The note ID.
     * @return DoctorNote|null The updated note or null if not found.
     */
    public function togglePin(int $id): ?DoctorNote
    {
        $note = $this->find($id);

        if (!$note) {
            return null;
        }

        $note->update(['is_pinned' => !$note->is_pinned]);

        return $note->fresh();
    }

    /**
     * Get pinned notes for a patient.
     *
     * @param int $patientId The patient ID.
     * @return Collection List of pinned notes.
     */
    public function getPinnedNotes(int $patientId): Collection
    {
        return $this->model
            ->with(['doctor.user'])
            ->where('patient_id', $patientId)
            ->where('is_pinned', true)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
