<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\PlayController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

/*
I had the idea of creating 2 Controllers : one of them for admin exclusive functions and the other for user access
This has to be revised, I am not sure about it...
For the moment we have one user-specific and other for statistics
*/ 

// Route::apiResource('plays', PlayController::class); // Study this Resource : creates ALL routes based on controller methods

Route::post('/players', [PlayerController::class, 'store']); // Create a player
Route::get('/players', [PlayerController::class, 'index']); // List all players with the average success rate of each one of them
Route::get('/players/ranking', [PlayerController::class, 'ranking']); // Average success across all plays (of all players)
Route::get('/players/ranking/loser', [PlayerController::class, 'loser']); // Returns player with lowest success rate
Route::get('/players/ranking/winner', [PlayerController::class, 'winner']); // Returns player with highest success rate

Route::middleware('auth:sanctum')->group(function () {
Route::put('/players/{id}', [PlayController::class, 'update']); // Update player name
Route::post('/players/{id}/games', [PlayController::class, 'play']); // Player rolls the dice and store outcome in db
Route::delete('/players/{id}/games', [PlayController::class, 'destroy']); // Delete player's plays
Route::get('/players/{id}/games', [PlayController::class, 'history']); // List all plays for a given player
});

Route::post('/register', [AuthController::class, 'register']); // Retiurns confirmation message for user's registration
Route::post('/login', [AuthController::class, 'login']); // Retiurns confirmation message for user's log in
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum'); // Deletes user's tokens and returns confirmation message for user's log out

?>