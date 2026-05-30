<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[Fillable(['name', 'address', 'phone', 'logo_url', 'manager_id', 'working_hours', 'is_active'])]
class Clinic extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $casts = [
        'working_hours' => 'array',
        'is_active' => 'boolean',
    ];

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function doctors(): BelongsToMany
    {
        return $this->belongsToMany(Doctor::class, 'doctor_clinic')
            ->withPivot('department_id', 'consultation_fee', 'session_duration_minutes', 'is_active', 'working_hours')
            ->withTimestamps()
            ->wherePivot('is_active', true);
    }

    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class, 'clinic_department')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
}
