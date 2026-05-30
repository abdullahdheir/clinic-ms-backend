<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Override;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'national_id', 'date_of_birth', 'gender', 'phone'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'date_of_birth' => 'date',
    ];

    public function doctor(): HasOne
    {
        return $this->hasOne(Doctor::class);
    }

    public function managedClinic(): HasOne
    {
        return $this->hasOne(Clinic::class, 'manager_id');
    }

    public function clinic(): HasOne
    {
        return $this->hasOne(Clinic::class, 'manager_id');
    }

    public function getClinicIdAttribute(): ?int
    {
        if ($this->isManager()) {
            return $this->managedClinic?->id;
        }
        if ($this->isDoctor()) {
            return $this->doctor?->clinics?->first()?->id;
        }
        if ($this->isReceptionist()) {
            // Receptionist can be associated with a clinic through a relationship
            // For now, return the first clinic or null
            return Clinic::first()?->id;
        }
        return null;
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'patient_id');
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class, 'patient_id');
    }

    public function medicalRecord(): HasOne
    {
        return $this->hasOne(MedicalRecord::class, 'patient_id');
    }

    public function medicalFiles(): HasMany
    {
        return $this->hasMany(MedicalFile::class, 'patient_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'patient_id');
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class, 'patient_id');
    }

    public function medicalReports(): HasMany
    {
        return $this->hasMany(MedicalReport::class, 'patient_id');
    }

    public function xrayImages(): HasMany
    {
        return $this->hasMany(XrayImage::class, 'patient_id');
    }

    public function doctorNotes(): HasMany
    {
        return $this->hasMany(DoctorNote::class, 'patient_id');
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function isManager(): bool
    {
        return $this->hasRole('manager');
    }

    public function isDoctor(): bool
    {
        return $this->hasRole('doctor');
    }

    public function isReceptionist(): bool
    {
        return $this->hasRole('receptionist');
    }

    public function isPatient(): bool
    {
        return $this->hasRole('patient');
    }

    #[Override]
    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'admin' ? $this->isSuperAdmin() : false;
    }

    public function getRoleAttribute(): string
    {
        return implode(', ', $this->roles()->pluck('name')->toArray());
    }
}
