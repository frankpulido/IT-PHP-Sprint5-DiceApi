<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function register (Request $request) {

        $request->headers->set('Accept', 'application/json');
        $validated = $request->validate([
            'name' => 'required|string|max:255',
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

        $user = User::create([
            'name' => $validated['name'],
            'nickname' => $validated['nickname'] ?? 'anonymous',
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = $user->createToken($request->name);

        return response()->json([
            'message' => 'Player created successfully.',
            'player' => $user,
            'your_token' => $token->plainTextToken
        ], 201);
    }

    public function login (Request $request) {

        $request->headers->set('Accept', 'application/json');
        $request->validate([
            'email' => 'required|email|exists:users',
            'password' => 'required'
        ]);

        // Case 1 : invalid email). Validate method breaks execution before reaching this line.
        $user = User::where('email', $request->email)->first(); // Case 1 (invalid email) is detected here.
        
        // Case 2: Password is incorrect (401 Unauthorized)
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Incorrect password. Try again.',
            ], 401);
        }

        $token = $user->createToken($user->name);

        return response()->json([
            'message' => 'User successfully logged in.',
            'player' => $user,
            'your_token' => $token->plainTextToken
        ], 200);
    }

    public function logout (Request $request) {

        $request->headers->set('Accept', 'application/json');

        $request->user()->tokens()->delete();
        return response()->json([
            'message' => 'User successfully logged out.'
        ], 200);
    }
}
