<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservationService extends Model
{
    use HasFactory;

    protected $table = 'reservation_service';

    protected $fillable = [
        'reservation_id',
        'service_id',
    ];

    public $timestamps = false;

    protected $dates = ['created_at'];

    // Relationships
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
