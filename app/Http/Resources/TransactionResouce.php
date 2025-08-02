<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResouce extends JsonResource
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
            'amount' => $this->amount,
            'charge' => $this->charge,
            'trx_type' => $this->trx_type,
            'trx_id' => $this->trx_id,
            'remarks' => $this->remarks,
            'created_at' => $this->created_at,
            'base_currency' => basicControl()->base_currency,
            'currency_symbol' => basicControl()->currency_symbol
        ];
    }
}
