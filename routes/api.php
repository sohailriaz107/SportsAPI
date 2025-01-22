<?php

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
Route::get('list',[UserController::class,'list']);
Route::post('/league',[LeagueController::class,'store']);
Route::post('/teams',[TeamsController::class,'store']);

Route::post('/matches',[MatchController::class,'store']);
Route::get('/matches/today',[MatchController::class,'getTodayMatches']);
Route::put('update/matches/{id}',[MatchController::class,'update']);
Route::post('delete/matches/{id}',[MatchController::class,'destroy']);
// score
Route::post('/storescore',[ScoreController::class,'store']);
Route::get('/getscore',[ScoreController::class,'index']);
Route::get('/showscore/{id}',[ScoreController::class,'show']);
Route::put('/updatescore/{id}',[ScoreController::class,'update']);
Route::post('/delete/{id}',[ScoreController::class,'destroy']);