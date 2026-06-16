<?php

namespace App\Http\Resources\Public;

use Dedoc\Scramble\Attributes\SchemaName;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

#[SchemaName("Public.PromoResource")]
class PromoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $originalPrice = (float) $this->original_price;
        $promoPrice = (float) $this->promo_price;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'image_url' => $this->image ? asset('storage/promos/' . $this->image) : null,
            'detail' => $this->detail,
            'original_price' => $originalPrice,
            'promo_price' => $promoPrice,
            'discount_percentage' => $originalPrice > 0
                ? round((($originalPrice - $promoPrice) / $originalPrice) * 100, 0)
                : 0,
        ];
    }
}
