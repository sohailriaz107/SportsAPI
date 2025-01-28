<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class API extends Model
{
    //
    protected $table ='api';
    protected $fillable = [
        'leages',
        'matches',
        'teams',
       
    ];
}
