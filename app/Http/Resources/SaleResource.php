<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleResource extends JsonResource
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
            'folio' => 'V-' . str_pad($this->id, 4, "0", STR_PAD_LEFT),
            'has_credit' => $this->has_credit,
            'total' => $this->total,
            'client' => $this->whenLoaded('client'),
            'products' => $this->whenLoaded('products'),
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
            'created_at' => $this->created_at?->isoFormat('DD MMM YYYY, h:mm a'),
            'updated_at' => $this->updated_at?->isoFormat('DD MMM YYYY, h:mm a'),
        ];
    }
}
