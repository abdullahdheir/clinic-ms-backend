<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicalReport extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'visit_id',
        'title',
        'description',
        'report_type',
        'file_path',
        'file_type',
        'report_date',
        'results',
        'recommendations',
        'status',
    ];

    protected $casts = [
        'report_date' => 'date',
        'status' => 'string',
    ];

    // العلاقات
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function visit()
    {
        return $this->belongsTo(Visit::class);
    }

    // نطاقات البحث
    public function scopeForPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('report_type', $type);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
