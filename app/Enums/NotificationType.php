<?php

namespace App\Enums;

enum NotificationType: string
{
    case APPOINTMENT_REMINDER = 'appointment_reminder';
    case APPOINTMENT_CONFIRMED = 'appointment_confirmed';
    case APPOINTMENT_CANCELLED = 'appointment_cancelled';
    case NEW_INVOICE = 'new_invoice';
    case PAYMENT_RECEIVED = 'payment_received';
    case VISIT_COMPLETED = 'visit_completed';
    case DOCTOR_ASSIGNED = 'doctor_assigned';
    case CLINIC_UPDATED = 'clinic_updated';
    case SYSTEM_MAINTENANCE = 'system_maintenance';

    public function getLabel(): string
    {
        return match($this) {
            self::APPOINTMENT_REMINDER => 'تذكير بالموعد',
            self::APPOINTMENT_CONFIRMED => 'تأكيد الموعد',
            self::APPOINTMENT_CANCELLED => 'إلغاء الموعد',
            self::NEW_INVOICE => 'فاتورة جديدة',
            self::PAYMENT_RECEIVED => 'استلام الدفعة',
            self::VISIT_COMPLETED => 'إتمام الزيارة',
            self::DOCTOR_ASSIGNED => 'تعيين طبيب',
            self::CLINIC_UPDATED => 'تحديث العيادة',
            self::SYSTEM_MAINTENANCE => 'صيانة النظام',
        };
    }

    public function getIcon(): string
    {
        return match($this) {
            self::APPOINTMENT_REMINDER => 'calendar',
            self::APPOINTMENT_CONFIRMED => 'check-circle',
            self::APPOINTMENT_CANCELLED => 'x-circle',
            self::NEW_INVOICE => 'file-text',
            self::PAYMENT_RECEIVED => 'dollar-sign',
            self::VISIT_COMPLETED => 'user-check',
            self::DOCTOR_ASSIGNED => 'user-plus',
            self::CLINIC_UPDATED => 'building',
            self::SYSTEM_MAINTENANCE => 'settings',
        };
    }

    public function getColor(): string
    {
        return match($this) {
            self::APPOINTMENT_REMINDER => '#3B82F6',
            self::APPOINTMENT_CONFIRMED => '#10B981',
            self::APPOINTMENT_CANCELLED => '#EF4444',
            self::NEW_INVOICE => '#F59E0B',
            self::PAYMENT_RECEIVED => '#10B981',
            self::VISIT_COMPLETED => '#8B5CF6',
            self::DOCTOR_ASSIGNED => '#3B82F6',
            self::CLINIC_UPDATED => '#F59E0B',
            self::SYSTEM_MAINTENANCE => '#6B7280',
        };
    }
}
