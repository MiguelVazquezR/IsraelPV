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
        $status = null;

            if ($this->paid_at) {
                $status = [
                    'label' => 'Pagado',
                    'color' => 'text-green-500',
                ];
            } else {
                $status = [
                    'label' => 'Pendiente',
                    'color' => 'text-red-600',
                ];
            }

        return [
            'id' => $this->id,
            'folio' => 'V-' . str_pad($this->id, 4, "0", STR_PAD_LEFT),
            'has_credit' => $this->has_credit,
            'paid_at' => $this->paid_at?->isoFormat('DD MMM YYYY'),
            'limit_date' => $this->limit_date?->isoFormat('DD MMM YYYY'),
            'total' => $this->total,
            'status' => $status,
            'client' => $this->whenLoaded('client'),
            'products' => $this->whenLoaded('products'),
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
            'created_at' => $this->created_at?->isoFormat('DD MMM YYYY, h:mm a'),
            'updated_at' => $this->updated_at?->isoFormat('DD MMM YYYY, h:mm a'),
        ];
    }
}
