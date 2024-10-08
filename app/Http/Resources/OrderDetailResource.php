<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;



class OrderDetailResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'name' => $this->product->name ?? '',
            'menu_id' => $this->menu_id,
            'product_id' => $this->product_id,
            'price' => $this->price,
            'discount' => $this->discount,
            'qty' => $this->qty,
            'total_price' => $this->price * $this->qty,
        ];
    }
}
