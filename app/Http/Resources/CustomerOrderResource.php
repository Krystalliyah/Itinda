<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerOrderResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this['id'],
            'customer_id' => $this['customer_id'],
            'store_id' => $this['store_id'],
            'order_number' => $this['order_number'] ?? 'NA',
            'status' => $this['status'] ?? 'pending',
            'total_amount' => (float) ($this['total_amount'] ?? 0),
            'pickup_date' => $this['pickup_date'] ?? null,
            'notes' => $this['notes'] ?? null,
            'created_at' => $this['created_at'],
            'updated_at' => $this['updated_at'],
            'store' => [
                'id' => $this['store']['id'] ?? 'NA',
                'name' => $this['store']['name'] ?? 'NA',
                'logo_url' => $this['store']['logo_url'] ?? null,
            ],
            'items' => collect($this['items'] ?? [])->map(function ($item) {
                return [
                    'id' => $item['id'],
                    'inventory_id' => $item['inventory_id'] ?? $item['product']['id'] ?? 0,
                    'quantity' => $item['quantity'] ?? 0,
                    'product' => [
                        'id' => $item['product']['id'] ?? 0,
                        'name' => $item['product']['name'] ?? 'NA',
                        'description' => $item['product']['description'] ?? 'NA',
                        'price' => (float) ($item['product']['price'] ?? 0),
                        'image_url' => $item['product']['image_url'] ?? null,
                    ],
                    'created_at' => $item['created_at'] ?? null,
                ];
            })->values(),
        ];
    }
}