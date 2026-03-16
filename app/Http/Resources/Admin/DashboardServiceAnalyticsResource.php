<?php

namespace App\Http\Resources\Admin;

use Dedoc\Scramble\Attributes\SchemaName;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

#[SchemaName("Admin.DashboardServiceAnalyticsResource")]
class DashboardServiceAnalyticsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $totalReservations = (int) $this->total_reservations;

        return [
            'service_id' => $this->id,
            'service_name' => $this->name,
            'reservation_count' => $totalReservations,
            'total_reservations' => $totalReservations,
        ];
    }
}
