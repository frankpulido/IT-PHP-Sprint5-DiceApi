<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Play;


class PlayControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $playerToken;
    protected $wrongToken = "i_am_a_wrong_token";

    public function setUp(): void
    {
        parent::setUp();

        // Create player user and token
        $player = User::factory()->create([
            'name' => 'Existent Player',
            'email' => 'existent_mail@test.com',
            'role' => 'player', // Adjust based on your role logic
        ]);

        $this->playerToken = $player->createToken('PlayerToken')->plainTextToken;

        // Assign plays
        Play::factory()->createMany([
            // 12 plays and 4 successes
            ['user_id' => $player->id, 'dice1' => 1, 'dice2' => 2],
            ['user_id' => $player->id, 'dice1' => 2, 'dice2' => 4],
            ['user_id' => $player->id, 'dice1' => 1, 'dice2' => 3],
            ['user_id' => $player->id, 'dice1' => 2, 'dice2' => 2],
            ['user_id' => $player->id, 'dice1' => 3, 'dice2' => 4], // success
            ['user_id' => $player->id, 'dice1' => 1, 'dice2' => 1],
            ['user_id' => $player->id, 'dice1' => 2, 'dice2' => 3],
            ['user_id' => $player->id, 'dice1' => 4, 'dice2' => 5],
            ['user_id' => $player->id, 'dice1' => 5, 'dice2' => 4],
            ['user_id' => $player->id, 'dice1' => 1, 'dice2' => 3],
            ['user_id' => $player->id, 'dice1' => 2, 'dice2' => 4],
            ['user_id' => $player->id, 'dice1' => 6, 'dice2' => 1], // success
            ['user_id' => $player->id, 'dice1' => 2, 'dice2' => 5], // success
            ['user_id' => $player->id, 'dice1' => 3, 'dice2' => 4], // success
            ['user_id' => $player->id, 'dice1' => 2, 'dice2' => 2],
            ['user_id' => $player->id, 'dice1' => 1, 'dice2' => 1],
        ]);
    }

    public function test_update_pass() : void
    {
        $playerId = 1;
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->playerToken,
        ])->putJson('/api/players/{$player-Id}', [
                'name' => 'Updated name',
                'nickname' => 'Updated nickname'
        ]);

        $response->assertStatus(200)
                ->assertJson(['message' => 'User data updated successfully.']);
    }

    public function test_update_fail_forbidden_access() : void
    {
        $playerId = 1;
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->wrongToken,
        ])->putJson("/api/players/{$playerId}");

        $response->assertStatus(401)
                ->assertJson(['message' => 'Unauthorized.']);
    }

    public function test_play_pass() : void
    {}

    public function test_play_fail_forbidden_access() : void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->playerToken,
        ])->postJson('/api/players/{{$player->id}}/games');

        $response->assertStatus(403)
                ->assertJson(['message' => 'Forbidden.']);
    }

    public function test_history_pass() : void
    {}

    public function test_history_fail_forbidden_access() : void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->playerToken,
        ])->getJson('/api/players/{{$player->id}}/games');

        $response->assertStatus(403)
                ->assertJson(['message' => 'Forbidden.']);
    }

    public function test_destroy_pass() : void
    {}

    public function test_destroy_fail_forbidden_access() : void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->playerToken,
        ])->deleteJson('/api/players/{{$player->id}}/games');

        $response->assertStatus(403)
                ->assertJson(['message' => 'Forbidden.']);
    }


}
