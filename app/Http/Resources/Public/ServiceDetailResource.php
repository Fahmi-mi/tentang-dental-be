<?php

namespace App\Http\Resources\Public;

use Dedoc\Scramble\Attributes\SchemaName;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

#[SchemaName("Public.ServiceDetailResource")]
class ServiceDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'detail' => $this->detail,
            'icon_url' => $this->icon ? asset('storage/services/' . $this->icon) : null,
            'article_content' => $this->article_content,
            'support_image_url' => $this->support_image ? asset('storage/services/' . $this->support_image) : null,
        ];
    }
}
