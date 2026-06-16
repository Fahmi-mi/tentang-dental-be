<?php

namespace App\Http\Resources\Admin;

use Dedoc\Scramble\Attributes\SchemaName;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

#[SchemaName("Admin.AdminProfileResource")]
class AdminProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'profile_image_url' => $this->profileImageUrl(),
            'created_at' => optional($this->created_at)->format('Y-m-d H:i:s'),
        ];
    }

    private function profileImageUrl(): string
    {
        if ($this->profile_image && Storage::disk('public')->exists('admins/' . $this->profile_image)) {
            return asset('storage/admins/' . $this->profile_image);
        }

        return asset('images/default-profile.svg');
    }
}
