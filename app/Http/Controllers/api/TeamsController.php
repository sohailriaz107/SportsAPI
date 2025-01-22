<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\teams;
use Illuminate\Http\Request;

class TeamsController extends Controller
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
        try {
            // **Validation**
            $request->validate([
                'api_id' => 'required|integer|unique:teams,api_id', 
                'sport_id' => 'required|integer',
                'country_id' => 'required|integer',
                'venue_id' => 'required|integer',
                'gender' => 'required|string|in:male,female,mixed',
                'name' => 'required|string|max:255',
                'short_code' => 'nullable|string|max:10',
                'image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'type' => 'required|string|max:50',
                'founded' => 'nullable|integer|min:1800|max:' . date('Y'),
            ]);
    
            // **Store Team Data**
            $team = new teams();
            $team->api_id = $request->api_id;
            $team->sport_id = $request->sport_id;
            $team->country_id = $request->country_id;
            $team->venue_id = $request->venue_id;
            $team->gender = $request->gender;
            $team->name = $request->name;
            $team->short_code = $request->short_code;
            $team->type = $request->type;
            $team->founded = $request->founded;
    
            // **Handle Image Upload**
            if ($request->hasFile('image_path')) {
                $imageName = time() . '.' . $request->file('image_path')->extension();
                $request->file('image_path')->move(public_path('uploads/teams'), $imageName);
                $team->image_path = 'uploads/teams/' . $imageName;
            }
    
            $team->save();
    
            return response()->json([
                'message' => 'Team registered successfully',
                'team' => $team,
            ], 201);
    
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong!',
                'error' => $e->getMessage(),
            ], 500);
        }
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
