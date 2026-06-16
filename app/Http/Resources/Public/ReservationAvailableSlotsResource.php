<?php

namespace App\Http\Resources\Public;

use Dedoc\Scramble\Attributes\SchemaName;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

#[SchemaName("Public.ReservationAvailableSlotsResource")]
class ReservationAvailableSlotsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $slots = [];

        if (is_array($this->resource) && array_key_exists('slots', $this->resource)) {
            $slots = is_array($this->resource['slots']) ? $this->resource['slots'] : [];
        }

        return [
            'slots' => $slots,
        ];
    }
}
