<?php

namespace App\Http\Resources\Admin;

use Dedoc\Scramble\Attributes\SchemaName;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

#[SchemaName("Admin.GalleryResource")]
class GalleryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'image_url' => $this->image ? asset('storage/galleries/' . $this->image) : null,
            'caption' => $this->caption,
            'created_at' => optional($this->created_at)->format('Y-m-d H:i:s'),
            'updated_at' => optional($this->updated_at)->format('Y-m-d H:i:s'),
        ];
    }
}
