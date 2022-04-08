<?php

namespace App\Http\Resources\Wallet;

use Illuminate\Http\Resources\Json\JsonResource;

class WalletResource extends JsonResource
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
            'id'=>(string)$this->id,
                'attributes'=>[
                    'owner'         => $this->user,
                    'type'          => $this->walletType,
                    'transactions'  => $this->walletLedgers,
                    'balance'       => $this->raw_balance,
                    'created_at'    => $this->created_at,
                ]
        ];
    }
    public function with($request)
    {
        return [
            'Status' => 'OK'
        ];
    }
}
