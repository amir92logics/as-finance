<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'product_image' => getFile(optional($this->product)->driver,optional($this->product)->thumbnail_image),
            'title' => optional(optional($this->product)->details)->title,
            'price' => currencyPosition($this->price + 0),
            'quantity' => $this->quantity,
            'subtotal' => currencyPosition($this->quantity * $this->price)
        ];
    }
}
