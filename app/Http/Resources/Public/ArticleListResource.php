<?php

namespace App\Http\Resources\Public;

use Dedoc\Scramble\Attributes\SchemaName;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

#[SchemaName("Public.ArticleListResource")]
class ArticleListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'image_url' => $this->image ? asset('storage/articles/' . $this->image) : null,
            'writer' => optional($this->admin)->name,
            'published_at' => optional($this->created_at)->format('d M Y'),
            'published_at_full' => optional($this->created_at)->format('Y-m-d H:i:s'),
        ];
    }
}
