<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class XrayImage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'visit_id',
        'title',
        'description',
        'image_type',
        'file_path',
        'thumbnail_path',
        'xray_date',
        'findings',
        'impression',
    ];

    protected $casts = [
        'xray_date' => 'date',
    ];

    protected $appends = ['image_url'];

    // العلاقات
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function visit()
    {
        return $this->belongsTo(Visit::class);
    }

    // وصولات
    public function getImageUrlAttribute()
    {
        return $this->file_path ? url('storage/' . $this->file_path) : null;
    }

    // نطاقات البحث
    public function scopeForPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('image_type', $type);
    }
}
