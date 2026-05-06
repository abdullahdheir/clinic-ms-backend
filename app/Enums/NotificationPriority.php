<?php

namespace App\Enums;

enum NotificationPriority: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case URGENT = 'urgent';

    public function getLabel(): string
    {
        return match($this) {
            self::LOW => 'منخفض',
            self::MEDIUM => 'متوسط',
            self::HIGH => 'عالي',
            self::URGENT => 'عاجل',
        };
    }

    public function getNumericValue(): int
    {
        return match($this) {
            self::LOW => 1,
            self::MEDIUM => 2,
            self::HIGH => 3,
            self::URGENT => 4,
        };
    }

    public function getColor(): string
    {
        return match($this) {
            self::LOW => '#6B7280',
            self::MEDIUM => '#F59E0B',
            self::HIGH => '#EF4444',
            self::URGENT => '#DC2626',
        };
    }
}
