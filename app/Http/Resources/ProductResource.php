<?php

namespace App\Http\Resources;

use App\Models\ProductDetails;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'category_id' => $this->category_id,
            'subcategory_id' => $this->subcategory_id,
            'price' => $this->price,
            'thumbnail_image' => getFile($this->driver,$this->thumbnail_image),
            'quantity' => $this->quantity,
            'quantity_unit' => $this->quantity_unit,
            'avg_rating' => $this->averageRating,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'details' => new ProductDetailsResouce($this->details),
            'wishlist' => wishlist($this->wishlist),
            'reviews' => ReviewResource::collection($this->reviews)
        ];
    }
}
