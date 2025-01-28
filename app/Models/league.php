<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class league extends Model

{
    protected $table="leagues";
    protected $fillable = [
        'api_id',
        'sport_id',
        'country_id',
        'name',
        'active',
        'short_code',
        'image_path',
        'sub_type',
        'category'
    ];
    protected $casts = [
        'has_jerseys' => 'boolean',
    ];
    
}
