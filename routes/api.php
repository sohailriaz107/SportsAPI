<?php

use App\Http\Controllers\api\APIcontroller;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\api\LeagueController;
use App\Http\Controllers\Api\MatchController;
use App\Http\Controllers\Api\TeamsController;
use App\Http\Controllers\ScoreController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('test',function(){
 return ["name"=>"Anil","channel"=>"Code"];
});

Route::get('/leagues',[APIcontroller::class,'FillLeague']);
Route::get('/teams',[APIcontroller::class,'FillTeams']);
Route::get('/matches',[APIcontroller::class,'FillMatches']);
Route::get('/livescore',[APIcontroller::class,'getLiveMatches']);


