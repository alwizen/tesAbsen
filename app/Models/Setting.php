<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'latitude',
        'longitude',
        'radius',
    ];

    protected $casts = [
        'radius' => 'integer',
    ];
}
