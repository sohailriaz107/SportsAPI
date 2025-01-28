<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\API;
use App\Models\League;
use App\Models\Matches;
use App\Models\teams;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
class APIcontroller extends Controller
{
    //
    protected $Api_token = 'ZR9GZiwYYiqn0brFsYqaOTAxiWtE0u5epuwwojxFGQsmLcLkDJFzDSM7eKu3';
    // leage APi
    public function FillLeague(Request $request)
    {
        // Fetch Data from API
        $response = Http::withOptions([
            'verify' => false,
        ])->withHeaders([
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
            'Authorization' =>  $this->Api_token,
        ])->get('https://api.sportmonks.com/v3/football/leagues');

        $data = $response->json();

        // ✅ Validate API Response
        if (!isset($data['data']) || empty($data['data'])) {
            return response()->json(['error' => 'Invalid API response', 'response' => $data], 500);
        }

        // ✅ Update or Create Leagues Data
        foreach ($data['data'] as $leagueDataFromApi) {
            League::updateOrCreate(
                ['api_id' => $leagueDataFromApi['id']],
                [
                    'name'           => $leagueDataFromApi['name'] ?? 'Unknown',
                    'sport_id'       => $leagueDataFromApi['sport_id'] ?? 0,
                    'country_id'     => $leagueDataFromApi['country_id'] ?? 0,
                    'active'         => $leagueDataFromApi['active'] ?? null,
                    'short_code'     => $leagueDataFromApi['short_code'] ?? null,
                    'image_path'     => $leagueDataFromApi['image_path'] ?? null,
                    'type'           => $leagueDataFromApi['type'] ?? null,
                    'sub_type'       => $leagueDataFromApi['sub_type'] ?? null,
                    'category'       => $leagueDataFromApi['category'] ?? null,
                    'last_played_at' => $leagueDataFromApi['last_played_at'] ?? null,
                    'has_jerseys'    => $leagueDataFromApi['has_jerseys'] ?? null,
                ]
            );
        }


        try {
            // Extract 'current_page' from the API response pagination data
            $currentPage = $data['pagination']['current_page'] ?? null;

            // If current page is available, update it
            if ($currentPage !== null) {
                Api::updateOrCreate(
                    ['id' => 1],
                    ['leages' => $currentPage]
                );

                return response()->json(['message' => 'Leagues data updated, page number store!']);
            } else {
                return response()->json(['error' => 'Current page is missing in API response'], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    //   teamApi
    public function FillTeams()
    {

        $lastPage = API::where('id', 1)->value('teams');
        $currentPage = $lastPage ? $lastPage : 1;

        // Loop through pages until no more pages are available
        while (true) {
            $response = Http::withOptions([
                'verify' => false,
            ])->withHeaders([
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
                'Authorization' => $this->Api_token,
            ])->get('https://api.sportmonks.com/v3/football/teams', [
                'page' => $currentPage // Pass the current page number to the API request
            ]);

            // Check if the response was successful
            if ($response->failed()) {
                return response()->json(['error' => 'Failed to fetch teams'], 500);
            }

            $data = $response->json();

            // Check if the response contains 'data'
            if (!isset($data['data']) || empty($data['data'])) {
                break; // Stop the loop if no data is returned
            }

            foreach ($data['data'] as $teamDataFromApi) {
                // Use updateOrCreate to prevent duplicate records based on api_id
                Teams::updateOrCreate(
                    ['api_id' => $teamDataFromApi['id']], // Check by API ID to prevent duplicates
                    [
                        'name'          => $teamDataFromApi['name'] ?? 'Unknown',
                        'sport_id'      => $teamDataFromApi['sport_id'] ?? 0,
                        'country_id'    => $teamDataFromApi['country_id'] ?? 0,
                        'venue_id'      => $teamDataFromApi['venue_id'] ?? null,
                        'gender'        => $teamDataFromApi['gender'] ?? null,
                        'short_code'    => $teamDataFromApi['short_code'] ?? null,
                        'image_path'    => $teamDataFromApi['image_path'] ?? null,
                        'founded'       => $teamDataFromApi['founded'] ?? null,
                        'type'          => $teamDataFromApi['type'] ?? null,
                        'placeholder'   => $teamDataFromApi['placeholder'] ?? null,
                        'last_played_at' => $teamDataFromApi['last_played_at'] ?? null
                    ]
                );
            }

            // Check if there are more pages based on the 'has_more' flag in the API response
            $hasMore = $data['pagination']['has_more'] ?? false; // Check the 'has_more' flag

            if ($hasMore) {
                // Increment the page number for the next request
                $currentPage++;
            } else {
                // No more pages, stop the loop
                break;
            }
        }

        // ✅ Store the last page number in the 'API' table
        try {
            API::updateOrCreate(
                ['id' => 1],
                ['teams' => $currentPage] // Store the last processed page number
            );

            return response()->json(['message' => 'Teams data stored from all available pages, last page updated']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    // matchesAPi
    public function FillMatches(Request $request)
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
                'Authorization' => $this->Api_token,
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
    // live score
    public function getLiveMatches()
    {
        // Fetch live matches from Sportmonks API
        $response = Http::withOptions([
            'verify' => false,
        ])->withHeaders([
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
            'Authorization' => $this->Api_token,
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
}
