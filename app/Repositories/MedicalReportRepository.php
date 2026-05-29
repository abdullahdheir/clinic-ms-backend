<?php

namespace App\Repositories;

use App\Models\MedicalReport;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class MedicalReportRepository extends BaseRepository
{
    /**
     * MedicalReportRepository constructor.
     *
     * @param MedicalReport $model The medical report model instance.
     */
    public function __construct(MedicalReport $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all reports for a patient with optional type filter.
     *
     * @param int $patientId The patient ID.
     * @param string|null $type The report type filter.
     * @return Collection List of medical reports.
     */
    public function getByPatient(int $patientId, ?string $type = null): Collection
    {
        $query = $this->model
            ->with(['doctor.user'])
            ->where('patient_id', $patientId)
            ->orderBy('report_date', 'desc');

        if ($type) {
            $query->where('report_type', $type);
        }

        return $query->get();
    }

    /**
     * Find report by ID with all relationships.
     *
     * @param int $id The report ID.
     * @return MedicalReport|null
     */
    public function findWithRelations(int $id): ?MedicalReport
    {
        return $this->model
            ->with(['doctor.user', 'patient.user', 'visit'])
            ->find($id);
    }

    /**
     * Create a new medical report with optional file upload.
     *
     * @param array $data The report data.
     * @param mixed|null $file The uploaded file.
     * @return MedicalReport The created report.
     */
    public function createWithFile(array $data, mixed $file = null): MedicalReport
    {
        if ($file) {
            $path = $file->store('medical_reports', 'public');
            $data['file_path'] = $path;
            $data['file_type'] = $file->extension();
        }

        $data['status'] = $data['status'] ?? 'completed';

        return $this->create($data);
    }

    /**
     * Update medical report with optional new file.
     *
     * @param int $id The report ID.
     * @param array $data The update data.
     * @param mixed|null $file The new uploaded file.
     * @return MedicalReport The updated report.
     */
    public function updateWithFile(int $id, array $data, mixed $file = null): MedicalReport
    {
        $report = $this->findOrFail($id);

        if ($file) {
            // Delete old file if exists
            if ($report->file_path) {
                Storage::disk('public')->delete($report->file_path);
            }

            $path = $file->store('medical_reports', 'public');
            $data['file_path'] = $path;
            $data['file_type'] = $file->extension();
        }

        $report->update($data);

        return $report->fresh();
    }

    /**
     * Delete report and its associated file.
     *
     * @param int $id The report ID.
     * @return bool True if deleted successfully.
     */
    public function deleteWithFile(int $id): bool
    {
        $report = $this->findOrFail($id);

        if ($report->file_path) {
            Storage::disk('public')->delete($report->file_path);
        }

        return $report->delete();
    }

    /**
     * Get available report types.
     *
     * @return array List of report types.
     */
    public function getReportTypes(): array
    {
        return [
            ['id' => 'blood_test', 'name' => 'تحليل دم'],
            ['id' => 'urine_test', 'name' => 'تحليل بول'],
            ['id' => 'xray', 'name' => 'أشعة'],
            ['id' => 'ultrasound', 'name' => 'موجات صوتية'],
            ['id' => 'mri', 'name' => 'رنين مغناطيسي'],
            ['id' => 'ct_scan', 'name' => 'أشعة مقطعية'],
            ['id' => 'medical_report', 'name' => 'تقرير طبي عام'],
            ['id' => 'discharge_summary', 'name' => 'ملخص خروج'],
        ];
    }
}
