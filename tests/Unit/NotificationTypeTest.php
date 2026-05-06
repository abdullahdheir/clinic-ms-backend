<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Enums\NotificationType;
use App\Enums\NotificationPriority;

class NotificationTypeTest extends TestCase
{
    public function test_notification_type_enum_has_correct_values()
    {
        $expectedTypes = [
            'appointment_reminder',
            'appointment_confirmed',
            'appointment_cancelled',
            'new_invoice',
            'payment_received',
            'visit_completed',
            'doctor_assigned',
            'clinic_updated',
            'system_maintenance',
        ];

        foreach ($expectedTypes as $type) {
            $this->assertTrue(NotificationType::hasValue($type), "Missing type: {$type}");
        }
    }

    public function test_notification_type_has_arabic_labels()
    {
        $expectedLabels = [
            'appointment_reminder' => 'تذكير بموعد',
            'appointment_confirmed' => 'تأكيد موعد',
            'appointment_cancelled' => 'إلغاء موعد',
            'new_invoice' => 'فاتورة جديدة',
            'payment_received' => 'استلام دفعة',
            'visit_completed' => 'انتهاء الزيارة',
            'doctor_assigned' => 'تعيين طبيب',
            'clinic_updated' => 'تحديث العيادة',
            'system_maintenance' => 'صيانة النظام',
        ];

        foreach ($expectedLabels as $type => $label) {
            $notificationType = NotificationType::from($type);
            $this->assertEquals($label, $notificationType->getLabel(), "Wrong label for: {$type}");
        }
    }

    public function test_notification_type_has_icons()
    {
        $expectedIcons = [
            'appointment_reminder' => 'calendar',
            'appointment_confirmed' => 'check-circle',
            'appointment_cancelled' => 'x-circle',
            'new_invoice' => 'file-text',
            'payment_received' => 'dollar-sign',
            'visit_completed' => 'user-check',
            'doctor_assigned' => 'user-plus',
            'clinic_updated' => 'building',
            'system_maintenance' => 'settings',
        ];

        foreach ($expectedIcons as $type => $icon) {
            $notificationType = NotificationType::from($type);
            $this->assertEquals($icon, $notificationType->getIcon(), "Wrong icon for: {$type}");
        }
    }

    public function test_notification_type_has_colors()
    {
        $expectedColors = [
            'appointment_reminder' => '#3B82F6',
            'appointment_confirmed' => '#10B981',
            'appointment_cancelled' => '#EF4444',
            'new_invoice' => '#F59E0B',
            'payment_received' => '#10B981',
            'visit_completed' => '#8B5CF6',
            'doctor_assigned' => '#3B82F6',
            'clinic_updated' => '#F59E0B',
            'system_maintenance' => '#6B7280',
        ];

        foreach ($expectedColors as $type => $color) {
            $notificationType = NotificationType::from($type);
            $this->assertEquals($color, $notificationType->getColor(), "Wrong color for: {$type}");
        }
    }

    public function test_notification_priority_enum_has_correct_values()
    {
        $expectedPriorities = ['low', 'medium', 'high', 'urgent'];

        foreach ($expectedPriorities as $priority) {
            $this->assertTrue(NotificationPriority::hasValue($priority), "Missing priority: {$priority}");
        }
    }

    public function test_notification_priority_has_arabic_labels()
    {
        $expectedLabels = [
            'low' => 'منخفض',
            'medium' => 'متوسط',
            'high' => 'عالي',
            'urgent' => 'عاجل',
        ];

        foreach ($expectedLabels as $priority => $label) {
            $notificationPriority = NotificationPriority::from($priority);
            $this->assertEquals($label, $notificationPriority->getLabel(), "Wrong label for: {$priority}");
        }
    }

    public function test_notification_priority_has_colors()
    {
        $expectedColors = [
            'low' => '#6B7280',
            'medium' => '#F59E0B',
            'high' => '#EF4444',
            'urgent' => '#DC2626',
        ];

        foreach ($expectedColors as $priority => $color) {
            $notificationPriority = NotificationPriority::from($priority);
            $this->assertEquals($color, $notificationPriority->getColor(), "Wrong color for: {$priority}");
        }
    }
}
