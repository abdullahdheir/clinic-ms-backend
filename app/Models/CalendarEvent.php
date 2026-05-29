<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'title',
        'description',
        'start_time',
        'end_time',
        'event_type',
        'doctor_id',
        'clinic_id',
        'color',
        'is_all_day',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_all_day' => 'boolean',
    ];

    // العلاقات
    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    // نطاقات البحث
    public function scopeBetweenDates($query, $start, $end)
    {
        return $query->whereBetween('start_time', [$start, $end]);
    }

    public function scopeForDoctor($query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    public function scopeForClinic($query, $clinicId)
    {
        return $query->where('clinic_id', $clinicId);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_time', '>=', now());
    }
}
