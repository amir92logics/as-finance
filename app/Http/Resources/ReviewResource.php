<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
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
            'product_id' => $this->product_id,
            'rating' => $this->rating,
            'comment' => strip_tags(optional($this->comment)->comment),
            'user' => [
                'name' => optional($this->user)->fullname,
                'image' => getFile(optional($this->user)->image_driver,optional($this->user)->image),
                'username' => optional($this->user)->username
            ],
            'created_at' => $this->created_at
        ];
    }
}
