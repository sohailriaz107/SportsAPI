<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\league;
use Illuminate\Http\Request;

class LeagueController extends Controller
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
    public function show(string $id)
    {
        //
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
