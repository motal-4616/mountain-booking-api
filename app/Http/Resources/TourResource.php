<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TourResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'image' => $this->image ? asset($this->image) : null,
            'gallery' => $this->gallery ? array_map(fn($img) => asset($img), $this->gallery) : [],
            'difficulty' => $this->difficulty,
            'difficulty_text' => $this->difficulty_text,
            'min_people' => $this->min_people,
            'duration_days' => $this->duration_days,
            'location' => $this->location,
            'altitude' => $this->altitude,
            'best_time' => $this->best_time,
            'description' => $this->description,
            'overview' => $this->overview,
            'itinerary' => $this->itinerary,
            'includes' => $this->includes,
            'excludes' => $this->excludes,
            'highlights' => $this->highlights,
            'map_lat' => $this->map_lat ? (float) $this->map_lat : null,
            'map_lng' => $this->map_lng ? (float) $this->map_lng : null,
            'is_active' => $this->is_active,
            'rating' => $this->when($this->relationLoaded('reviews'), function() {
                $approved = $this->reviews->where('status', 'approved');
                return $approved->count() > 0 ? round($approved->avg('rating'), 1) : 0;
            }),
            'reviews_count' => $this->when($this->relationLoaded('reviews'), function() {
                return $this->reviews->where('status', 'approved')->count();
            }),
            'is_wishlisted' => (bool) ($this->is_wishlisted ?? false),
            'next_schedule' => $this->when($this->relationLoaded('schedules'), function() {
                $nextSchedule = $this->schedules()
                    ->where('is_active', true)
                    ->where('departure_date', '>=', now())
                    ->where('available_slots', '>', 0)
                    ->orderBy('departure_date')
                    ->first();
                
                return $nextSchedule ? new ScheduleResource($nextSchedule) : null;
            }),
            'price_min' => $this->when($this->relationLoaded('schedules'), function() {
                return $this->schedules()
                    ->where('is_active', true)
                    ->where('departure_date', '>=', now())
                    ->where('available_slots', '>', 0)
                    ->min('price');
            }),
            'price_max' => $this->when($this->relationLoaded('schedules'), function() {
                return $this->schedules()
                    ->where('is_active', true)
                    ->where('departure_date', '>=', now())
                    ->where('available_slots', '>', 0)
                    ->max('price');
            }),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
