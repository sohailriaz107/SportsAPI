<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Models\Matches;
use Illuminate\Http\Request;

use function PHPUnit\Framework\matches;

class MatchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'api_id' => 'required|integer|unique:matches,api_id',
            'sport_id' => 'required|integer',
            'league_id' => 'required|integer',
            'season_id' => 'nullable|integer',
            'stage_id' => 'nullable|integer',
            'group_id' => 'nullable|integer',
            'aggregate_id' => 'nullable|integer',
            'round_id' => 'nullable|integer',
            'state_id' => 'nullable|integer',
            'venue_id' => 'nullable|integer',
            'name' => 'string|max:255',
            'starting_at' => 'nullable|date_format:Y-m-d H:i:s',
            'result_info' => 'nullable|string|max:255',
            'leg' => 'nullable|string|max:255',
            'details' => 'nullable|string|max:255',
            'length' => 'nullable|integer',
            'placeholder' => 'nullable|string|max:255',
            'has_odds' => 'nullable|string|max:255',
            'starting_at_timestamp' => 'nullable|integer',
        ]);
    
        $startDate = Carbon::now();
        $daysToStore = 30; // Store matches for the next 30 days
    
        // Loop through the next 30 days and create matches
        for ($id = 0; $id < $daysToStore; $id++) {
            $matchDate = $startDate->copy()->addDays($id)->setTime(rand(0, 23), rand(0, 59), 0)->toDateTimeString();

    
            // Generate a unique and meaningful match name
            $matchName = 'Match-' . $matchDate . '-Sport-' . ($request->sport_id) . '-League-' . ($request->league_id);
    
            Matches::updateOrCreate(
                [
                    'starting_at' => $matchDate,
                    'api_id' => $request->api_id + $id, // Ensure uniqueness
                ],
                [
                    'status' => 'upcoming',
                    'api_id' => $request->api_id + $id,
                    'sport_id' => $request->sport_id + $id,
                    'league_id' => $request->league_id + $id,
                    'season_id' => $request->season_id + $id,
                    'stage_id' => $request->stage_id + $id,
                    'group_id' => $request->group_id + $id,
                    'aggregate_id' => $request->aggregate_id + $id,
                    'round_id' => $request->round_id + $id,
                    'state_id' => $request->state_id + $id,
                    'venue_id' => $request->venue_id + $id,
                    'name' => $matchName,
                    'starting_at_timestamp' => strtotime($matchDate),
                    'result_info' => $request->result_info,
                    'leg' => $request->leg,
                    'details' => $request->details,
                    'length' => $request->length,
                    'placeholder' => $request->placeholder,
                    'has_odds' => $request->has_odds,
                ]
            );
        }
    
        return response()->json(['message' => 'Matches for the next 30 days have been stored successfully.']);
    }
    

    




    /**
     * Display the specified resource.
     */
    public function getTodayMatches()
    {
        //
        $today = Carbon::now()->toDateString(); // Get today's date (YYYY-MM-DD)
    
        $matches = Matches::whereDate('starting_at', $today)->get(); // Get matches for today
    
        return response()->json([
            'date' => $today,
            'total_matches' => $matches->count(),
            'matches' => $matches,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Validate request data
    $request->validate([
        'api_id' => 'sometimes|integer|unique:matches,api_id,' . $id,
        'sport_id' => 'sometimes|integer',
        'league_id' => 'sometimes|integer',
        'season_id' => 'nullable|integer',
        'stage_id' => 'nullable|integer',
        'group_id' => 'nullable|integer',
        'aggregate_id' => 'nullable|integer',
        'round_id' => 'nullable|integer',
        'state_id' => 'nullable|integer',
        'venue_id' => 'nullable|integer',
        'name' => 'sometimes|string|max:255',
        'starting_at' => 'sometimes|date_format:Y-m-d H:i:s',
        'result_info' => 'nullable|string|max:255',
        'leg' => 'nullable|string|max:255',
        'details' => 'nullable|string|max:255',
        'length' => 'nullable|integer',
        'placeholder' => 'nullable|string|max:255',
        'has_odds' => 'nullable|in:yes,no',
        'starting_at_timestamp' => 'nullable|integer',
        'result_info' => 'sometimes|string|in:upcoming,ongoing,completed,canceled'
    ]);

    // Find match by ID
    $match = Matches::find($id);

    if (!$match) {
        return response()->json([
            'message' => 'Match not found.'
        ], 404);
    }

    // Update match fields
    $match->update($request->all());

    return response()->json([
        'message' => 'Match updated successfully.',
        'match' => $match
    ]);
}
    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $match=matches::find($id);
        if (!$match) {
            return response()->json([
               'message' => 'Match not found.'
            ], 404);
        }
        $match->delete();
        return response()->json([
           'message' => 'Match deleted successfully.'
        ]);
    }
}
