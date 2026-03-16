<?php

namespace App\Http\Resources\Admin;

use Dedoc\Scramble\Attributes\SchemaName;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

#[SchemaName("Admin.DashboardMonthlyAnalyticsResource")]
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
