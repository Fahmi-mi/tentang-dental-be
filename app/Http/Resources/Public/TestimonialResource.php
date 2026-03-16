<?php

namespace App\Http\Resources\Public;

use Dedoc\Scramble\Attributes\SchemaName;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

#[SchemaName("Public.TestimonialResource")]
class TestimonialResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'rating' => $this->rating,
            'testimoni' => $this->testimoni,
            'photo_url' => $this->photo ? asset('storage/testimonials/' . $this->photo) : null,
            'created_at' => optional($this->created_at)->format('d M Y'),
        ];
    }
}
