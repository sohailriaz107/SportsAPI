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
        $request->validate([
            'match_id'   => 'required|exists:matches,league_id',
            'team_1_id'  => 'required|exists:teams,sport_id',
            'team_2_id'  => 'required|exists:teams,sport_id',
            'team_1_score' => 'required|integer|min:0',
            'team_2_score' => 'required|integer|min:0',
            'status'      => 'required|in:upcoming,live,finished',
        ]);
        $score = score::create($request->all());

        return response()->json(['message' => 'Score created successfully', 'score' => $score], 201);
    }
     /**
     * Display a specific score with related match and teams.
     */
    public function show($id)
    {
        $score = score::with([ 'team1', 'team2'])->find($id);
        
        if (!$score) {
            return response()->json(['message' => 'Score not found'], 404);
        }

        return response()->json($score);
    }
     /**
     * Update a score.
     */

    public function update(Request $request, $id)
    {
     // Find the existing score record
     $score = Score::find($id);

     if (!$score) {
         return response()->json(['message' => 'Score not found'], 404);
     }
 
     // Log the incoming request data
    //  \Log::info('Request data for update:', $request->all());
 
     // Validate the incoming request data
     $request->validate([
         'team_1_score' => 'integer|min:0',
         'team_2_score' => 'integer|min:0',
         'status'       => 'in:upcoming,live,finished',
     ]);
 
     // Update only the fields provided in the request
     $updated = $score->update($request->only(['team_1_score', 'team_2_score', 'status']));
 
     if ($updated) {
         // If the update is successful, return a response with the updated score
         return response()->json(['message' => 'Score updated successfully', 'score' => $score], 201);
     } else {
         // If the update fails, return a failure message
         return response()->json(['message' => 'Failed to update the score'], 500);
     }
 }
 public function destroy($id){
    $score=score::find($id);
    if(!$score){
        return response()->json(['message'=>'score not found']);
    }
    $score->delete();
    return response()->json(['message'=>'score deleted successfully']);
 }
}
