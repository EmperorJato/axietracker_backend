<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class DashboardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'datetime' => Carbon::parse($this->datetime)->format('d/m/Y H:i'),
            'reward' => $this->reward,
            'energy' => $this->energy,
            'mmr' => $this->mmr,
            'pvp' => $this->pvp,
            'draw' => $this->draw,
            'pvp_total' => $this->pvp_total,
            'slp' => $this->slp,
            'slp_inventory' => $this->slp_inventory,
        ];
    }
}
