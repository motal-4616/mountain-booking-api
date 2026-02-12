<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TourSimpleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * Simplified version for nested resources
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'image' => $this->image ? asset($this->image) : null,
            'difficulty' => $this->difficulty,
            'difficulty_text' => $this->difficulty_text,
            'location' => $this->location,
            'altitude' => $this->altitude,
        ];
    }
}
