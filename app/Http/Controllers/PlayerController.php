<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PlayerController extends Controller
{
    // Create a new player (must be registered first and having email address confirmed)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            //'nickname' => 'nullable|string|max:255',
            'nickname' => [
                'nullable',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    if ($value !== 'anonymous' && User::where('nickname', $value)->exists()) {
                        $fail('The nickname must be unique unless it is "anonymous".');
                    }
                },
            ],
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed' // "confirm" means that 'password' == 'password_confirmation' in $request
        ]);

        $player = User::create([
            'name' => $validated['name'],
            'nickname' => $validated['nickname'] ?? 'anonymous',
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'message' => 'Player created successfully',
            'player' => $player,
        ], 201);
    }

    // List all players with their success rates
    public function index()
    {
        /*
        // Restrict to admin
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        */
        return "Here comes the list of ALL players and their average success rate";
    }

    // Average success across all plays (all players)
    public function ranking()
    {
        return "Average success across all plays (of all players)";
    }

    // Player with the lowest success rate
    public function loser()
    {
        return "Returns player with LOWEST success rate";
    }

    // Player with the highest success rate
    public function winner()
    {
        return "Returns player with HIGHEST success rate";
    }
}
