<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tour_id' => $this->tour_id,
            'departure_date' => $this->departure_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'max_people' => $this->max_people,
            'available_slots' => $this->available_slots,
            'booked_slots' => $this->max_people - $this->available_slots,
            'price' => (float) $this->price,
            'min_people' => $this->min_people,
            'registration_deadline_days' => $this->registration_deadline_days,
            'registration_deadline' => $this->registration_deadline?->format('Y-m-d'),
            'is_active' => $this->is_active,
            'is_available' => $this->is_available,
            'is_full' => $this->is_full,
            'can_register' => $this->can_register,
            'status_text' => $this->status_text,
            'duration_days' => $this->duration_days,
            'tour' => $this->when($this->relationLoaded('tour'), function() {
                return new TourSimpleResource($this->tour);
            }),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
