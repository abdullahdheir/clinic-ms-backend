<?php

namespace App\Models;

use App\Enums\NotificationPriority;
use App\Enums\NotificationType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'title', 'message', 'type', 'priority', 'is_read', 'link', 'data'])]
class Notification extends Model
{
    use HasFactory;
    
    protected $casts = [
        'is_read' => 'boolean',
        'data' => 'array',
        'type' => NotificationType::class,
        'priority' => NotificationPriority::class,
    ];

    protected $attributes = [
        'priority' => NotificationPriority::MEDIUM,
    ];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getTypeLabel(): string
    {
        return $this->type?->getLabel() ?? '';
    }

    public function getTypeIcon(): string
    {
        return $this->type?->getIcon() ?? 'bell';
    }

    public function getTypeColor(): string
    {
        return $this->type?->getColor() ?? '#6B7280';
    }

    public function getPriorityLabel(): string
    {
        return $this->priority?->getLabel() ?? '';
    }

    public function getPriorityColor(): string
    {
        return $this->priority?->getColor() ?? '#6B7280';
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeByPriority($query, NotificationPriority $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeByType($query, NotificationType $type)
    {
        return $query->where('type', $type);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
