<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingPaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_id' => $this->booking_id,
            'amount' => (float) $this->amount,
            'payment_type' => $this->payment_type,
            'payment_type_text' => $this->payment_type_text,
            'payment_method' => $this->payment_method,
            'payment_method_text' => $this->payment_method_text,
            'status' => $this->status,
            'status_text' => $this->status_text,
            'transaction_ref' => $this->transaction_ref,
            'note' => $this->note,
            'paid_at' => $this->paid_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
