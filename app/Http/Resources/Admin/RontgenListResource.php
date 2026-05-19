<?php

namespace App\Http\Resources\Admin;

use Dedoc\Scramble\Attributes\SchemaName;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

#[SchemaName("Admin.RontgenListResource")]
class RontgenListResource extends JsonResource
{
    public function toArray(Request $request): array
{
    return [
        'id' => $this->id,
        'patient' => [
            'id' => optional($this->patient)->id,
            'name' => optional($this->patient)->name,
            'phone' => optional($this->patient)->phone,
            'age'   => optional($this->patient)->age,
        ],
        'doctor' => [
            'id' => optional($this->doctor)->id,
            'name' => optional($this->doctor)->name,
        ],
        'latest_image_url' => $this->latest_image_url,
        'detail' => $this->detail,
        'status' => $this->status,
        'target_foto' => $this->target_foto,
        'tags' => $this->whenLoaded('tags', function () {
            return $this->tags->map(function ($tag) {
                return [
                    'id' => $tag->id,
                    'tag_name' => $tag->tag_name,
                ];
            })->values();
        }),
        'examination_images' => $this->whenLoaded('examinationImages', function () {
            return $this->examinationImages->map(function ($img) {
                return [
                    'image_type' => $img->image_type,
                    'image_url'  => asset('storage/rontgen/' . $img->image_path),
                ];
            })->values();
        }),
        'created_at' => optional($this->created_at)->format('Y-m-d H:i:s'),
    ];
}
}
