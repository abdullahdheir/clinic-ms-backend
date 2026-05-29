<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'specialty', 'max_capacity', 'description'])]
class Department extends Model
{
    use HasFactory;

    public function clinics(): BelongsToMany
    {
        return $this->belongsToMany(Clinic::class, 'clinic_department')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function doctors(): BelongsToMany
    {
        return $this->belongsToMany(Doctor::class, 'doctor_department')
            ->withPivot('is_primary')
            ->withTimestamps();
    }
}
