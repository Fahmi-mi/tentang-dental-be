<?php

namespace App\Http\Resources\Admin;

use Dedoc\Scramble\Attributes\SchemaName;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

#[SchemaName("Admin.PatientListResource")]
class PatientListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'birth_date' => $this->birth_date,
            'gender' => $this->gender,
            'age' => $this->age,
            'created_at' => optional($this->created_at)->format('Y-m-d H:i:s'),
        ];
    }
}
