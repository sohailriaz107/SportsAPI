<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Matches extends Model
{
    //
    protected $table="matches";
    protected $fillable = [
        'api_id',
        'sport_id',
        'league_id',
        'season_id',
        'stage_id',
        'group_id',
        'aggregate_id',
        'round_id',
        'state_id',
        'venue_id',
        'name',
        'starting_at',
        'result_info',
        'leg',
        'details',
        'length',
        'placeholder',
        'has_odds',
        'starting_at_timestamp',
    ];
}
