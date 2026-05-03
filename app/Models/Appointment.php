<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['patient_id', 'doctor_id', 'clinic_id', 'department_id', 'scheduled_at', 'status', 'reason', 'notes', 'checked_in_at', 'checked_in_by'])]
class Appointment extends Model
{
    protected $casts = ['scheduled_at' => 'datetime', 'checked_in_at' => 'datetime'];


    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function checkedInBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    public function visit(): HasOne
    {
        return $this->hasOne(Visit::class);
    }
}
