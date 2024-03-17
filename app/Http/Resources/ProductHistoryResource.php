<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductHistoryResource extends JsonResource
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
            'folio' => 'E-' . str_pad($this->id, 4, "0", STR_PAD_LEFT),
            'type' => $this->type,
            'quantity' => $this->quantity,
            'notes' => $this->notes,
            'product' => $this->whenLoaded('product'),
            'created_at' => $this->created_at?->isoFormat('DD MMM YYYY'),
            'updated_at' => $this->updated_at?->isoFormat('DD MMM YYYY'),
        ];
    }
}
