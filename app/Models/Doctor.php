<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'bio', 'specialization'])]
class Doctor extends Model
{
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function clinics(): BelongsToMany
    {
        return $this->belongsToMany(Clinic::class, 'doctor_clinic')
            ->withPivot('department_id', 'consultation_fee', 'session_duration_minutes', 'is_active', 'working_hours')
            ->withTimestamps();
    }

    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class, 'doctor_department')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function department()
    {
        return $this->belongsToMany(Department::class, 'doctor_department')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(DoctorShift::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    public function medicalReports(): HasMany
    {
        return $this->hasMany(MedicalReport::class);
    }

    public function xrayImages(): HasMany
    {
        return $this->hasMany(XrayImage::class);
    }

    public function doctorNotes(): HasMany
    {
        return $this->hasMany(DoctorNote::class);
    }

    public function calendarEvents(): HasMany
    {
        return $this->hasMany(CalendarEvent::class);
    }
}
