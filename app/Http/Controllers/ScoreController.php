<?php

namespace App\Http\Controllers;

use App\Models\score;
use Illuminate\Http\Request;

class ScoreController extends Controller
{
    //
    public function index(){
        $scores = score::with(['match', 'team1', 'team2'])->get();
        return response()->json($scores);
    }

    public function store(Request  $request){
      
    }
     /**
     * Display a specific score with related match and teams.
     */
    public function show($id)
    {
       
    }
     /**
     * Update a score.
     */

    public function update(Request $request, $id)
    {
    
 }
 public function destroy($id){
    
 }
}
