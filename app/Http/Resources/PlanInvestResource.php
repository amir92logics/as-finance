<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanInvestResource extends JsonResource
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
            'plan_name' => optional($this->plan)->plan_name,
            'profit' => $this->profit,
            'return_period' => $this->returnPeriod(),
            'total_return' => $this->total_return,
            'next_return' => $this->next_return,
            'format_next_return_date' => dateTime($this->next_return),
            'base_currency' => basicControl()->base_currency,
            'currency_symbol' => basicControl()->currency_symbol,
            'percent' => $this->next_payment_percent
        ];
    }
}
