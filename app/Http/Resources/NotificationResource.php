<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Notification
 */
class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type,
            'priority' => $this->priority,
            'is_read' => $this->is_read,
            'link' => $this->link,
            'data' => $this->data,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Computed fields from accessors
            'type_label' => $this->getTypeLabel(),
            'type_icon' => $this->getTypeIcon(),
            'type_color' => $this->getTypeColor(),
            'priority_label' => $this->getPriorityLabel(),
            'priority_color' => $this->getPriorityColor(),
        ];
    }
}
