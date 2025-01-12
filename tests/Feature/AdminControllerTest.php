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
            'name' => 'Existent Player',
            'email' => 'existent_mail@test.com',
            'role' => 'player', // Adjust based on your role logic
        ]);

        $this->playerToken = $player->createToken('PlayerToken')->plainTextToken;

        // Create players
        $players = User::factory()->count(4)->sequence(
            ['name' => 'Player 1', 'nickname' => 'anonymous'],
            ['name' => 'Player 2', 'nickname' => 'anonymous'],
            ['name' => 'Player 3', 'nickname' => 'anonymous'],
            ['name' => 'Player 4', 'nickname' => 'anonymous']
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

    public function test_store_pass_creates_user_player() : void
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

    public function test_store_fails_forbidden_access() : void
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

    public function test_store_fails_existent_email() : void{
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/players', [
            'name' => 'Test Player Anonymous',
            'email' => 'existent_mail@test.com', // same email created in setUp() above
            'password' => 'temporary_24hours_password',
            'password_confirmation' => 'temporary_password',
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
    }

    public function test_store_fails_mismatch_confirmation_password() : void{
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/players', [
            'name' => 'Test Player Anonymous',
            'email' => 'test_anonymous@test.com',
            'password' => 'temporary_24hours_password',
            'password_confirmation' => 'temporary_password', // mismatching confirmation
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['password']);
    }

    public function test_index_pass_retrieve_players_with_success_rate() : void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/players');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'total_players',
                'players' => [
                    '*' => ['id', 'name', 'nickname', 'total_throws', 'average_success_rate'],
                ],
            ])
            ->assertJson(['message' => 'List of all players and their average success rates.'
        ]);

        // Assert total players
        $this->assertEquals(6, $response->json('total_players')); // 2 users created in setUp for token also count

        // Assert individual player stats
        $players = $response->json('players');

        // Admin User (no plays)
        $this->assertEquals(0, $players[0]['total_throws']);
        $this->assertEquals('0.0000', $players[0]['average_success_rate']); 

        // Player User (no plays)
        $this->assertEquals(0, $players[1]['total_throws']);
        $this->assertEquals('0.0000', $players[1]['average_success_rate']); 

        // Player 1
        $this->assertEquals(4, $players[2]['total_throws']);
        $this->assertEquals('0.0000', $players[2]['average_success_rate']); 

        // Player 2
        $this->assertEquals(4, $players[3]['total_throws']);
        $this->assertEquals('0.2500', $players[3]['average_success_rate']); 

        // Player 3
        $this->assertEquals(4, $players[4]['total_throws']);
        $this->assertEquals('0.2500', $players[4]['average_success_rate']); 

        // Player 4
        $this->assertEquals(4, $players[5]['total_throws']);
        $this->assertEquals('0.5000', $players[5]['average_success_rate']);
    }

    public function test_index_fail_forbidden_access() : void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->playerToken,
        ])->getJson('/api/players');

        $response->assertStatus(403)
                ->assertJson(['message' => 'Forbidden.']);
    }


    public function test_ranking_pass_average_success_rate() : void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/players/ranking');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'total_throws',
                    'total_successes',
                    'average_success_rate'
                ])
                ->assertJson([
                    'message' => 'Average success across all plays (all players).',
                    'total_throws' => 16,
                    'total_successes' => 4,
                    'average_success_rate' => 0.25
                ]);
    }

    public function test_ranking_fail_forbidden_access() : void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->playerToken,
        ])->getJson('/api/players/ranking');

        $response->assertStatus(403)
                ->assertJson(['message' => 'Forbidden.']);
    }

    public function test_winner_pass() : void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/players/ranking/winner');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'winner'
                ])
                ->assertJson([
                    'message' => 'Player with the HIGHEST success rate.',
                    'winner' => [
                        'id' => 6,
                        'name' => 'Player 4',
                        'nickname' => 'anonymous',
                        'total_throws'=> 4,
                        'average_success_rate' => 0.5000
                    ]
                ]);
    }

    public function test_winner_fail_forbidden_access() : void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->playerToken,
        ])->getJson('/api/players/ranking/winner');

        $response->assertStatus(403)
                ->assertJson(['message' => 'Forbidden.']);
    }

    public function test_loser_pass() : void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/players/ranking/loser');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'loser'
                ])
                ->assertJson([
                    'message' => 'Player with the LOWEST success rate.',
                    'loser' => [
                        'id' => 3,
                        'name' => 'Player 1',
                        'nickname' => 'anonymous',
                        'total_throws'=> 4,
                        'average_success_rate' => 0.0000
                    ]
                ]);
    }

    public function test_loser_fail_forbidden_access() : void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->playerToken,
        ])->getJson('/api/players/ranking/loser');

        $response->assertStatus(403)
                ->assertJson(['message' => 'Forbidden.']);
    }

}
