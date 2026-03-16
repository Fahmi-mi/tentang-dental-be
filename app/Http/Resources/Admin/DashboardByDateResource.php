<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
