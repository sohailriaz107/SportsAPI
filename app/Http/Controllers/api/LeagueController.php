<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\league;
use App\Models\APi;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class LeagueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
        // Get the current page from the request, default to 1
        $currentPage = $request->input('page', 1);
        // Define how many items you want per page
        $perPage = $request->input('per_page', 25);

        // Fetch the leagues with pagination
        $leagues = League::paginate($perPage);

        // Prepare the response
        $response = [
            'pagination' => [
                'count' => $leagues->total(), // Total number of items
                'per_page' => $leagues->perPage(), // Items per page
                'current_page' => $leagues->currentPage(), // Current page
                'next_page' => $leagues->hasMorePages() ? $leagues->currentPage() + 1 : null, // Next page
                'has_more' => $leagues->hasMorePages(), // Check if there are more pages
            ],
            'leagues' => $leagues->items(), // The actual items
            'rate_limit' => [
                'resets_in_seconds' => 1816, // Example value, adjust as needed
                'remaining' => 2995, // Example value, adjust as needed
                'requested_entity' => 'League', // Example value, adjust as needed
            ],
            'timezone' => 'UTC', // Example value, adjust as needed
        ];

        return response()->json($response);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'api_id' => 'required|integer|unique:leagues,api_id',
            'sport_id' => 'required|integer',
            'country_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'active' => 'required|boolean',
            'short_code' => 'nullable|string|max:10',
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Validate as an image
            'sub_type' => 'nullable|string|max:50',
            'category' => 'nullable|string|max:50',
        ]);

        $league = new League();
        $league->api_id = $request->api_id;
        $league->sport_id = $request->sport_id;
        $league->country_id = $request->country_id;
        $league->name = $request->name;
        $league->active = $request->active;
        $league->short_code = $request->short_code;
        $league->sub_type = $request->sub_type;
        $league->category = $request->category;

        // **Handle Image Upload Properly**
        if ($request->hasFile('image_path')) {
            $imageName = time() . '.' . $request->file('image_path')->extension();
            $request->file('image_path')->move(public_path('uploads'), $imageName);
            $league->image_path = 'uploads/' . $imageName;
        }

        $league->save();

        return response()->json([
            'message' => 'League registered successfully',
            'league' => $league,
        ], 201);
    }



    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        // Fetch Data from API
        $response = Http::withOptions([
            'verify' => false,
        ])->withHeaders([
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
            'Authorization' => 'ZR9GZiwYYiqn0brFsYqaOTAxiWtE0u5epuwwojxFGQsmLcLkDJFzDSM7eKu3'
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
