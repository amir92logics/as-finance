<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayoutResource extends JsonResource
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
            'amount' => $this->amount+0,
            'charge' => $this->charge+0,
            'amount_in_base_currency' =>  $this->amount_in_base_currency+0,
            'charge_in_base_currency' =>  $this->charge_in_base_currency+0,
            'net_amount_in_base_currency' =>  $this->net_amount_in_base_currency+0,
            'trx_id' => $this->trx_id,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'payout_currency_code' => $this->payout_currency_code,
            'base_currency' => basicControl()->base_currency,
            'currency_symbol' => basicControl()->currency_symbol,
            ''
        ];
    }
}
