<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RatePlan extends Model
{
    use HasFactory;

    protected $table = 'rate_plans';


    protected $fillable = [
        'title',
        'currency',
        'options',
        'meal_type',
        'rate_mode',
        'sell_mode',
        'property_id',
        'room_type_id',
        'id_rate_plans',
    ];

    protected $casts = [
        'options' => 'array', // Convierte automáticamente JSON en array
    ];
}

