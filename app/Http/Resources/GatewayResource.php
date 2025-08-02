<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GatewayResource extends JsonResource
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
            'code' => $this->code,
            'name' => $this->name,
            'image' => getFile($this->driver, $this->image),
            'sort_by' => $this->sort_by,
            'parameters' => $this->parameters,
            'currencies' => $this->currencies,
            'extra_parameters' => $this->extra_parameters,
            'supported_currency' => $this->supported_currency,
            'receivable_currencies' => $this->receivable_currencies,
            'description' => $this->description,
            'currency_type' => $this->currency_type,
            'is_sandbox' => $this->is_sandbox,
            'environment' => $this->environment,
            'is_manual' => $this->is_manual,
            'note' => $this->note,
            'created_at' => dateTime($this->created_at),
            'updated_at' => dateTime($this->updated_at),
        ];
    }
}
