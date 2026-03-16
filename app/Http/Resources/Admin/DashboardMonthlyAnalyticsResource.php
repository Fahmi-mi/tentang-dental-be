<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardMonthlyAnalyticsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'service_name' => $this->service_name,
            'total_reservations' => (int) $this->total_reservations,
        ];
    }
}
