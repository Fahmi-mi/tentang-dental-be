<?php

namespace App\Http\Resources\Admin;

use Dedoc\Scramble\Attributes\SchemaName;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

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
            ],
            'xray_image_url' => $this->getRontgenImageUrl($this->xray_image),
            'detail' => $this->detail,
            'created_at' => optional($this->created_at)->format('Y-m-d H:i:s'),
        ];
    }

    private function getRontgenImageUrl(?string $fileName): ?string
    {
        if (!$fileName) {
            return null;
        }

        if (Storage::disk('public')->exists('rontgen/' . $fileName)) {
            return asset('storage/rontgen/' . $fileName);
        }

        if (Storage::disk('public')->exists('rontgens/' . $fileName)) {
            return asset('storage/rontgens/' . $fileName);
        }

        return null;
    }
}
