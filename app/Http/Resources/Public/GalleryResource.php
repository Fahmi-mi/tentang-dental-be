<?php

namespace App\Http\Resources\Public;

use Dedoc\Scramble\Attributes\SchemaName;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

#[SchemaName("Public.GalleryResource")]
class GalleryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'image_url' => $this->image ? asset('storage/galleries/' . $this->image) : null,
            'caption' => $this->caption,
            'uploaded_at' => optional($this->created_at)->format('d M Y'),
        ];
    }
}
