<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_code' => $this->booking_code,
            'user_id' => $this->user_id,
            'schedule_id' => $this->schedule_id,
            'quantity' => $this->quantity,
            'contact_name' => $this->contact_name,
            'contact_phone' => $this->contact_phone,
            'contact_email' => $this->contact_email,
            'note' => $this->note,
            'total_amount' => (float) $this->total_amount,
            'discount_amount' => (float) $this->discount_amount,
            'final_price' => (float) $this->final_price,
            'status' => $this->status,
            'status_text' => $this->status_text,
            'status_badge_class' => $this->status_badge_class,
            'cancellation_reason' => $this->cancellation_reason,
            'cancelled_at' => $this->cancelled_at?->toIso8601String(),
            'refund_status' => $this->refund_status,
            'refund_message' => $this->refund_message,
            'refund_processed_at' => $this->refund_processed_at?->toIso8601String(),
            'can_cancel' => $this->can_cancel,
            'can_review' => $this->can_review,
            'schedule' => $this->when($this->relationLoaded('schedule'), function() {
                return new ScheduleResource($this->schedule);
            }),
            'user' => $this->when($this->relationLoaded('user'), function() {
                return new UserResource($this->user);
            }),
            'coupon' => $this->when($this->relationLoaded('coupon'), function() {
                return $this->coupon ? new CouponResource($this->coupon) : null;
            }),
            'payments' => $this->when($this->relationLoaded('payments'), function() {
                return BookingPaymentResource::collection($this->payments);
            }),
            'latest_payment' => $this->when($this->relationLoaded('payments'), function() {
                $latestPayment = $this->payments->first();
                return $latestPayment ? new BookingPaymentResource($latestPayment) : null;
            }),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
