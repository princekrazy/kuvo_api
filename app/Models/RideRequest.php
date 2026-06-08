<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RideRequest extends Model
{
    protected $fillable = [
        'user_id',
        'driver_id',
        'status',
        'fare',
        'status',
        'size',
        'driver_lat',
        'driver_lng',
        'origin_lat',
        'origin_lng',
        'destination_lat',
        'destination_lng',
        'distance_km',
        'estimated_minutes'
    ];
     
}
