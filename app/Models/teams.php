<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class teams extends Model
{
    //
    protected $table="teams";
    protected $fillable=['api_id','sport_id','country_id','venue_id','gender','name','short_code','image_path',	'type','founded'];
}
