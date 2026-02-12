<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WishlistResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tour_id' => $this->tour_id,
            'tour' => $this->when($this->relationLoaded('tour'), function() {
                return new TourResource($this->tour);
            }),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
