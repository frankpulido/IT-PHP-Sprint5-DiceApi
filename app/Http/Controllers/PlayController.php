<?php

namespace App\Http\Controllers;

use App\Models\Play;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class PlayController extends Controller
{
    // Roll the dice and store the new play
    public function play(Request $request)
    {
        $request->headers->set('Accept', 'application/json');
        $user = $request->user();

        // Roll both dice
        $dice1 = random_int(1, 6);
        $dice2 = random_int(1, 6);

        // Calculate success
        $success = ($dice1 + $dice2) === 7;

        // Create a new play
        $play = Play::create([
            'user_id' => $user->id, // I modified this line too
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
    public function destroy(Request $request)
    {
        $request->headers->set('Accept', 'application/json');
        $user = $request->user();
        Play::where('user_id', $user->id)->delete();

        return response()->json([
            'message' => "All plays for user {$user->nickname} have been deleted."
        ], 200);
    }


    // Get all plays for a specific player
    public function history(Request $request)
    {
        $request->headers->set('Accept', 'application/json');
        $user = $request->user();
        $plays = Play::where('user_id', $user->id)->get();

        return response()->json([
            'message' => "All plays for user {$user->nickname}",
            'plays' => $plays
        ], 200);
    }


    // Update a player's name
    public function update(Request $request)
    {
        $request->headers->set('Accept', 'application/json');
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $user->update([
            'name' => $validated['name'],
        ]);
    
        return response()->json([
            'message' => 'User name updated successfully.',
            'user' => $user,
        ], 200);
    }
    
}
