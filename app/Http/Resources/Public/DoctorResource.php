<?php

namespace App\Http\Resources\Public;

use Dedoc\Scramble\Attributes\SchemaName;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

#[SchemaName("Public.DoctorResource")]
class DoctorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'specialization' => $this->specialization,
            'photo_url' => $this->photo ? asset('storage/doctors/' . $this->photo) : null,
            'schedule' => $this->schedule,
            'statement' => $this->statement,
        ];
    }
}
