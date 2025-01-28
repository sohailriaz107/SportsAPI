<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Models\Matches;
use Illuminate\Support\Facades\Http;
use App\Models\API;
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
        $newMatchIds = [];

        //  today's date and next 30 days date
        $today = Carbon::now()->format('Y-m-d');
        $next30Days = Carbon::now()->addDays(30)->format('Y-m-d');

        $currentPage = 1;
        $hasMorePages = true;

        while ($hasMorePages) {

            $response = Http::withOptions([
                'verify' => false,
            ])->withHeaders([
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
                'Authorization' => 'ZR9GZiwYYiqn0brFsYqaOTAxiWtE0u5epuwwojxFGQsmLcLkDJFzDSM7eKu3'
            ])->get("https://api.sportmonks.com/v3/football/fixtures/between/{$today}/{$next30Days}?page={$currentPage}");

            //  Check API Response
            if (!$response->successful()) {
                return response()->json(['error' => 'API request failed', 'response' => $response->body()], 500);
            }

            $data = $response->json();

            if (!isset($data['data']) || empty($data['data'])) {
                return response()->json(['error' => "No data found", 'response' => $data], 500);
            }

            //  Store matches only in the next 30 days
            foreach ($data['data'] as $matchData) {
                if (!isset($matchData['id']) || !isset($matchData['starting_at'])) {
                    continue; // Skip invalid matches
                }

                $matchDate = Carbon::parse($matchData['starting_at']);

                if ($matchDate->between($today, $next30Days)) {
                    Matches::updateOrCreate(
                        ['api_id' => $matchData['id']],
                        [
                            'sport_id'      => $matchData['sport_id'] ?? null,
                            'league_id'     => $matchData['league_id'] ?? null,
                            'season_id'     => $matchData['season_id'] ?? null,
                            'stage_id'      => $matchData['stage_id'] ?? null,
                            'group_id'      => $matchData['group_id'] ?? null,
                            'aggregate_id'  => $matchData['aggregate_id'] ?? null,
                            'round_id'      => $matchData['round_id'] ?? null,
                            'state_id'      => $matchData['state_id'] ?? null,
                            'venue_id'      => $matchData['venue_id'] ?? null,
                            'name'          => $matchData['name'] ?? 'Unknown',
                            'starting_at'   => $matchData['starting_at'] ?? null,
                            'starting_at_timestamp' => strtotime($matchData['starting_at']),
                            'result_info'   => $matchData['result_info'] ?? null,
                            'leg'           => $matchData['leg'] ?? null,
                            'details'       => $matchData['details'] ?? null,
                            'length'        => $matchData['length'] ?? null,
                            'placeholder'   => $matchData['placeholder'] ?? null,
                            'has_odds'      => $matchData['has_odds'] ?? null,
                        ]
                    );
                    $newMatchIds[] = $matchData['id'];
                }
            }

            //Move to next page if available
            $hasMorePages = $data['pagination']['has_more'] ?? false;
            $currentPage++;
        }

        try {
            API::updateOrCreate(
                ['id' => 1],
                ['matches' => $currentPage] // Store the last processed page number
            );

            return response()->json(['message' => 'Matches stored from all available pages (Next 30 days only), last page updated']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }





    /**
     * Display the specified resource.
     */
    public function getLiveMatches()
    {
        // Fetch live matches from Sportmonks API
        $response = Http::withOptions([
            'verify' => false,
        ])->withHeaders([
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
            'Authorization' => 'ZR9GZiwYYiqn0brFsYqaOTAxiWtE0u5epuwwojxFGQsmLcLkDJFzDSM7eKu3'
        ])->get('https://api.sportmonks.com/v3/football/livescores/latest');
    
        // Check if API request was successful
        if ($response->failed()) {
            return response()->json(['error' => 'Failed to fetch live matches from API'], 500);
        }
    
        // Convert API response to JSON
        $apiMatches = $response->json();
    
        // Get current date and time
        $currentDateTime = now();

        $matches = Matches::whereDate('starting_at', $currentDateTime->toDateString()) // Sirf aaj ke matches
            ->where('starting_at', '<=', $currentDateTime) // Jo ab tak start ho chuke hain
            ->orderBy('starting_at', 'asc') // Sort by starting time
            ->get();
        
    
        // Return response with both database and API matches
        return response()->json([
            'live matches' => $matches,
            'api_matches' => $apiMatches
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
    public function destroy(string $id) {}
}
