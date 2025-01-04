<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\PlayController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

/*
I will create 2 Controllers : one of them for admin exclusive functions and the other for user access
This has to be revised, I am not sure about it...
PlayerController
AdminController
*/ 

Route::post('/players', [PlayerController::class, 'store']); // Create a player
Route::put('/players/{id}', [PlayerController::class, 'update']); // Update player name
Route::post('/players/{id}/games', [PlayController::class, 'store']); // Player rolls the dice and store outcome in db
Route::delete('/players/{id}/games', [PlayController::class, 'destroy']); // Delete player's plays
Route::get('/players', [PlayerController::class, 'index']); // List all players with the average success rate of each one of them
Route::get('/players/{id}/games', [PlayController::class, 'index']); // List all plays for a given player
Route::get('/players/ranking', [PlayerController::class, 'ranking']); // Average success across all plays (of all players)
Route::get('/players/ranking/loser', [PlayerController::class, 'loser']); // Returns player with lowest success rate
Route::get('/players/ranking/winner', [PlayerController::class, 'winner']); // Returns player with highest success rate

?>