<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class PlayerController extends Controller
{
    // Create a new player
    public function store(Request $request)
    {
        return "Here is the form to create a NEW Player";
    }

    // Update a player's name
    public function update($id, Request $request)
    {
        return "Here is the form to UPDATE name for Player with ID : $id";
    }

    // List all players with their success rates
    public function index()
    {
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
