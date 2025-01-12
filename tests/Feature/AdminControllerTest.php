<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Play;

class AdminControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $adminToken;
    protected $playerToken;

    public function setUp(): void
    {
        parent::setUp();

        // Create admin user and token
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'role' => 'admin', // We need an admin user
        ]);

        $this->adminToken = $admin->createToken('AdminToken')->plainTextToken;

        // Create player user and token
        $player = User::factory()->create([
            'name' => 'Player User',
            'role' => 'player', // Adjust based on your role logic
        ]);

        $this->playerToken = $player->createToken('PlayerToken')->plainTextToken;

        // Create players
        $players = User::factory()->count(4)->sequence(
            ['name' => 'Player 1'],
            ['name' => 'Player 2'],
            ['name' => 'Player 3'],
            ['name' => 'Player 4']
        )->create();

        // Assign plays
        Play::factory()->createMany([
            // Player 1: 0 successes
            ['user_id' => $players[0]->id, 'dice1' => 1, 'dice2' => 2],
            ['user_id' => $players[0]->id, 'dice1' => 2, 'dice2' => 4],
            ['user_id' => $players[0]->id, 'dice1' => 1, 'dice2' => 3],
            ['user_id' => $players[0]->id, 'dice1' => 2, 'dice2' => 2],

            // Player 2: 1 success
            ['user_id' => $players[1]->id, 'dice1' => 3, 'dice2' => 4], // success
            ['user_id' => $players[1]->id, 'dice1' => 1, 'dice2' => 1],
            ['user_id' => $players[1]->id, 'dice1' => 2, 'dice2' => 3],
            ['user_id' => $players[1]->id, 'dice1' => 4, 'dice2' => 5],

            // Player 3: 1 success
            ['user_id' => $players[2]->id, 'dice1' => 5, 'dice2' => 4],
            ['user_id' => $players[2]->id, 'dice1' => 1, 'dice2' => 3],
            ['user_id' => $players[2]->id, 'dice1' => 2, 'dice2' => 4],
            ['user_id' => $players[2]->id, 'dice1' => 6, 'dice2' => 1], // success

            // Player 4: 2 successes
            ['user_id' => $players[3]->id, 'dice1' => 2, 'dice2' => 5], // success
            ['user_id' => $players[3]->id, 'dice1' => 3, 'dice2' => 4], // success
            ['user_id' => $players[3]->id, 'dice1' => 2, 'dice2' => 2],
            ['user_id' => $players[3]->id, 'dice1' => 1, 'dice2' => 1],
        ]);
    }

    public function test_store_creates_user_player() : void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/players', [
            'name' => 'Test Player Anonymous',
            'email' => 'test_anonymous@test.com',
            'password' => 'temporary_24hours_password',
            'password_confirmation' => 'temporary_24hours_password',
        ]);

        $response->assertStatus(201)
                ->assertJsonStructure(['message', 'player', 'your_token'])
                ->assertJson(['message' => 'Player created successfully.']);
    }

    public function test_store_creates_user_player_fails() : void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->playerToken, // player user lacks permission
        ])->postJson('/api/players', [
            'name' => 'Test Player Anonymous',
            'email' => 'test_anonymous@test.com',
            'password' => 'temporary_24hours_password',
            'password_confirmation' => 'temporary_24hours_password',
        ]);

        $response->assertStatus(403)
                ->assertJsonStructure(['message'])
                ->assertJson(['message' => 'Forbidden.']);
    }

}
