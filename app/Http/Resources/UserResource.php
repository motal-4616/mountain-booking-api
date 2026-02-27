<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $levelInfo = $this->level_info;
        $nextLevelInfo = $this->next_level_info;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'avatar' => $this->avatar_url,
            'role' => $this->role,
            'role_text' => $this->role_text,
            'bio' => $this->bio,
            'address' => $this->address,
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'is_blocked' => $this->is_blocked,
            'current_level' => $this->current_level ?? 1,
            'level_discount' => (float) ($this->level_discount ?? 0),
            'level_info' => $levelInfo ? [
                'level' => $levelInfo->level,
                'name' => $levelInfo->name,
                'icon' => $levelInfo->icon,
                'frame_color' => $levelInfo->frame_color,
                'discount_percent' => (float) $levelInfo->discount_percent,
                'benefits' => $levelInfo->benefits,
            ] : null,
            'next_level_info' => $nextLevelInfo ? [
                'level' => $nextLevelInfo->level,
                'name' => $nextLevelInfo->name,
                'icon' => $nextLevelInfo->icon,
                'frame_color' => $nextLevelInfo->frame_color,
                'discount_percent' => (float) $nextLevelInfo->discount_percent,
                'required_tours' => $nextLevelInfo->required_tours,
                'required_reviews' => $nextLevelInfo->required_reviews,
                'required_blogs' => $nextLevelInfo->required_blogs,
            ] : null,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
