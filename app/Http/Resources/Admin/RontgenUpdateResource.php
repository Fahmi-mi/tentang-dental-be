<?php

namespace App\Http\Resources\Admin;

use Dedoc\Scramble\Attributes\SchemaName;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

#[SchemaName("Admin.RontgenUpdateResource")]
class RontgenUpdateResource extends JsonResource
{
    public function toArray(Request $request): array
{
    return [
        'id' => $this->id,
        'patient' => [
            'id' => optional($this->patient)->id,
            'name' => optional($this->patient)->name,
        ],
        'doctor' => [
            'id' => optional($this->doctor)->id,
            'name' => optional($this->doctor)->name,
        ],
        'latest_image_url' => $this->latest_image_url,
        'detail' => $this->detail,
        'status' => $this->status, 
        'updated_at' => optional($this->updated_at)->format('Y-m-d H:i:s'),
    ];
    }
}
