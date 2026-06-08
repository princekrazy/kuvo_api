<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'id_number',
        'vehicle_description',
        'license_number',
        'size',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
