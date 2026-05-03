<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['patient_id', 'blood_type', 'chronic_diseases', 'allergies', 'emergency_contact', 'medications', 'family_history', 'notes'])]
class MedicalRecord extends Model
{
    use HasFactory;
    protected $casts = [
        'chronic_diseases' => 'array',
        'allergies' => 'array',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }
}
