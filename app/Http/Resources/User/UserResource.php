<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
                    'name'         => $this->name,
                    'email'        => $this->email,
                    'wallets'       => $this->wallets,
                    'created_at'   => $this->created_at,
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
