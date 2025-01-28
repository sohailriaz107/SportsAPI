<?php

namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
use App\Models\teams;
use App\Models\API;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class TeamsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
        $currentPage = $request->input('page', 1);
    // Define how many items you want per page
    $perPage = $request->input('per_page', 25);

    // Fetch the leagues with pagination
    $team = teams::paginate($perPage);

    // Prepare the response
    $response = [
        'pagination' => [
            'count' => $team->total(), // Total number of items
            'per_page' => $team->perPage(), // Items per page
            'current_page' => $team->currentPage(), // Current page
            'next_page' => $team->hasMorePages() ? $team->currentPage() + 1 : null, // Next page
            'has_more' => $team->hasMorePages(), // Check if there are more pages
        ],
        'team' => $team->items(), // The actual items
        'rate_limit' => [
            'resets_in_seconds' => 1816, // Example value, adjust as needed
            'remaining' => 2995, // Example value, adjust as needed
            'requested_entity' => 'team', // Example value, adjust as needed
        ],
        'timezone' => 'UTC', // Example value, adjust as needed
    ];

    return response()->json($response);
    
    }

    /**
     * Store a newly created resource in storage.
     */
    
    
   
    public function store()
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
                'Authorization' => 'ZR9GZiwYYiqn0brFsYqaOTAxiWtE0u5epuwwojxFGQsmLcLkDJFzDSM7eKu3'
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
                        'last_played_at'=> $teamDataFromApi['last_played_at'] ?? null
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
    
    

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
