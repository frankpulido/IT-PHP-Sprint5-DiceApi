<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PlayController;

/*
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::get('/login', function () {
    return response()->json([
        'status' => false,
        'message' => 'Login route is not available for APIs.',
    ], 404);
});
*/

// Route::apiResource('plays', PlayController::class); // Study this Resource : creates ALL routes based on controller methods

Route::middleware('auth:sanctum')->group(function () {
Route::post('/players', [AdminController::class, 'store']); // Create a player
Route::get('/players', [AdminController::class, 'index']); // List all players with the average success rate of each one of them
Route::get('/players/ranking', [AdminController::class, 'ranking']); // Average success across all plays (of all players)
Route::get('/players/ranking/loser', [AdminController::class, 'loser']); // Returns player with lowest success rate
Route::get('/players/ranking/winner', [AdminController::class, 'winner']); // Returns player with highest success rate

Route::put('/players/{id}', [PlayController::class, 'update']); // Update player name
Route::post('/players/{id}/games', [PlayController::class, 'play']); // Player rolls the dice and store outcome in db
Route::delete('/players/{id}/games', [PlayController::class, 'destroy']); // Delete player's plays
Route::get('/players/{id}/games', [PlayController::class, 'history']); // List all plays for a given player
});

Route::post('/register', [AuthController::class, 'register']); // Returns confirmation message for user's registration
Route::post('/login', [AuthController::class, 'login']); // Returns confirmation message for user's log in
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum'); // Deletes user's tokens and returns confirmation message for user's log out

?>