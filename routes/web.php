<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\PlayController;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/players', [PlayerController::class, 'store']);       // Create a player
Route::put('/players/{id}', [PlayerController::class, 'update']);  // Update player name
Route::post('/players/{id}/games', [PlayController::class, 'store']); // Player rolls the dice and store outcome in db
Route::delete('/players/{id}/games', [PlayController::class, 'destroy']); // Delete player's plays
Route::get('/players', [PlayerController::class, 'index']);        // List all players with success rate
Route::get('/players/{id}/games', [PlayController::class, 'index']); // List all plays for a player
Route::get('/players/ranking', [PlayerController::class, 'ranking']); // Average success across all plays
Route::get('/players/ranking/loser', [PlayerController::class, 'loser']); // Player with lowest success rate
Route::get('/players/ranking/winner', [PlayerController::class, 'winner']); // Player with highest success rate


?>