<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\PlayController;

/*
 |--------------------------------------------------------------------------
 | API Routes
 |--------------------------------------------------------------------------
 |
 | Here is where you can register API routes for your application. These
 | routes are loaded by the RouteServiceProvider within a group which
 | is assigned the "api" middleware group. Enjoy building your API!
 |
 */

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('players')->group(function () {
Route::post('/', [PlayerController::class, 'store']);       // Create a player
Route::put('/{id}', [PlayerController::class, 'update']);   // Update player name
Route::post('/{id}/games', [PlayController::class, 'store']); // Player rolls the dice
Route::delete('/{id}/games', [PlayController::class, 'destroy']); // Delete player's plays
Route::get('/', [PlayerController::class, 'index']);        // List all players with success %
Route::get('/{id}/games', [PlayController::class, 'index']); // List all plays for a player
Route::get('/ranking', [PlayerController::class, 'ranking']); // Average success across all plays
Route::get('/ranking/loser', [PlayerController::class, 'loser']); // Player with lowest success %
Route::get('/ranking/winner', [PlayerController::class, 'winner']); // Player with highest success %
});

?>
