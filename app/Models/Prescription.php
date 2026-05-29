<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prescription extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'visit_id',
        'diagnosis',
        'notes',
        'prescription_date',
        'status',
    ];

    protected $casts = [
        'prescription_date' => 'date',
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

    public function medications()
    {
        return $this->hasMany(PrescriptionMedication::class);
    }

    // نطاقات البحث
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }
}
