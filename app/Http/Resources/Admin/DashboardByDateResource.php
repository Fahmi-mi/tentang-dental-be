<?php

namespace App\Http\Resources\Admin;

use Dedoc\Scramble\Attributes\SchemaName;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

#[SchemaName("Admin.DashboardByDateResource")]
class DashboardByDateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'date' => (string) $this->reservation_date,
            'total' => (int) $this->total,
        ];
    }
}
