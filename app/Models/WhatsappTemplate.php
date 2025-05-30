<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id',
        'name',
        'language',
        'status',
        'category',
        'parameter_format',
        'components',
    ];

    protected $casts = [
        'components' => 'array',
    ];
}
