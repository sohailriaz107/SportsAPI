<?php

namespace App\Models;
use App\Models\Matches;
use App\Models\Team;

use Illuminate\Database\Eloquent\Model;

class score extends Model
{
    protected $table ='scores';
    protected $fillable = [
        'match_id',
        'team_1_id',
        'team_2_id',
        'team_1_score',
        'team_2_score',
        'status'
    ];
    //
    public function match()
    {
        return $this->belongsTo(Matches::class);
    }

    public function team1()
    {
        return $this->belongsTo(teams::class, 'team_1_id');
    }

    public function team2()
    {
        return $this->belongsTo(teams::class, 'team_2_id');
    }
}
