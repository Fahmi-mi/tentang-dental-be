<?php

namespace App\Http\Resources\Admin;

use Dedoc\Scramble\Attributes\SchemaName;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

#[SchemaName("Admin.AdminLoginResource")]
class AdminLoginResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'admin' => new AdminProfileResource($this->resource['admin']),
            'token' => $this->resource['token'],
        ];
    }
}
