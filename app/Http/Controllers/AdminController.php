<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Play;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // Create a new player (must be registered first and having email address confirmed)
    public function store(Request $request)
    {
        $request->headers->set('Accept', 'application/json');
        
        // Restrict to admin
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

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

        $token = $player->createToken($request->name);

        return response()->json([
            'message' => 'Player created successfully.',
            'player' => $player,
            'your_token' => $token->plainTextToken
        ], 201);
    }


    // List all players with their success rates
    public function index(Request $request)
    {
        $request->headers->set('Accept', 'application/json');
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $users = User::all();
        $total_players = User::count();
        $players = [];
        // Calculate average success rate for each player and dinamically adds attribute 'average_success_rate'
        foreach ($users as $user) {
            $players[] = [
                'id' => $user->id,
                'name' => $user->name,
                'nickname' => $user->nickname,
                'total_throws' => $user->total_throws = $user->plays()->count() ?? 0,
                'average_success_rate' => number_format($user->plays()->avg('success') ?? 0, 4) // String but at least rounds decimals
            ];
        }

        return response()->json([
            'message' => 'List of all players and their average success rates.',
            'total_players' => $total_players,
            'players' => $players
        ], 200);
    }


    // Average success across all plays (all players)
    public function ranking(Request $request)
    {
        $request->headers->set('Accept', 'application/json');
        // Restrict to admin
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $total_throws = Play::count();
        $total_successes = Play::where('success', 1)->count();
        $average_success_rate = Play::avg('success') ?? 0;

        return response()->json([
            'message' => 'Average success across all plays (all players).',
            'total_throws' => $total_throws,
            'total_successes' => $total_successes,
            'average_success_rate' => $average_success_rate
        ], 200);
    }

    // Player with the lowest success rate
    public function loser(Request $request)
    {
        $request->headers->set('Accept', 'application/json');
        // Restrict to admin
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $players = collect($this->index($request)->original['players']);
        $players = $players->where('total_throws', '>', 0); // Filter players with total_throws > 0 using where
        $loser = $players->sortBy('average_success_rate')->first();

        return response()->json([
            'message' => 'Player with the LOWEST success rate.',
            'loser' => $loser,
        ], 200);
    }

    // Player with the highest success rate
    public function winner(Request $request)
    {
        $request->headers->set('Accept', 'application/json');
        // Restrict to admin
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $players = collect($this->index($request)->original['players']);
        $winner = $players->sortByDesc('average_success_rate')->first();

        return response()->json([
            'message' => 'Player with the HIGHEST success rate.',
            'loser' => $winner,
        ], 200);
    }
}
