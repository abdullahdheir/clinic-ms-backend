<?php

namespace App\Exports;

use App\Models\Appointment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AppointmentsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Appointment::with(['patient', 'doctor.user', 'clinic'])->get();
    }

    public function headings(): array
    {
        return [
            'المعرف',
            'المريض',
            'الطبيب',
            'العيادة',
            'الحالة',
            'الموعد',
            'ملاحظات',
            'تاريخ الإنشاء',
        ];
    }

    public function map($appointment): array
    {
        return [
            $appointment->id,
            $appointment->patient->name,
            $appointment->doctor->user->name,
            $appointment->clinic->name,
            match($appointment->status) {
                'confirmed'   => 'مؤكّد',
                'pending'     => 'انتظار',
                'cancelled'   => 'ملغي',
                'done'        => 'منتهي',
                'in_progress' => 'جارٍ',
                'no_show'     => 'لم يحضر',
                default       => $appointment->status,
            },
            $appointment->scheduled_at->format('d/m/Y H:i'),
            $appointment->notes,
            $appointment->created_at->format('d/m/Y H:i'),
        ];
    }
}
