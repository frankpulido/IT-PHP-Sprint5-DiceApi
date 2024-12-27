<?php

namespace App\Http\Controllers;

use App\Models\Play;
use App\Models\User;
use Illuminate\Http\Request;

class PlayController extends Controller
{
    // Roll the dice and store the new play
    public function store($id, Request $request)
    {
        // Ensure the user exists
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'Player not found'], 404);
        }

        // Roll both dice
        $dice1 = random_int(1, 6);
        $dice2 = random_int(1, 6);

        // Calculate success
        $success = ($dice1 + $dice2) === 7;

        // Create a new play
        $play = Play::create([
            'user_id' => $id,
            'dice1' => $dice1,
            'dice2' => $dice2,
            'success' => $success,
        ]);

        // Return the play details
        return response()->json([
            'message' => 'Play recorded successfully',
            'play' => $play,
        ], 201);
    }

    // Delete all plays for a specific player
    public function destroy($id)
    {
        //
    }

    // Get all plays for a specific player
    public function index($id)
    {
        //
    }
}
