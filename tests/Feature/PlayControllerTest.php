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
    protected $newcomerToken;
    protected $wrongToken = "i_am_a_wrong_token";

    public function setUp(): void
    {
        parent::setUp();

        // Create 2 player users and their tokens
        $player = User::factory()->create([
            'name' => 'Existent Player',
            'nickname' => 'uniqueNickname',
            'email' => 'existent_mail@test.com',
            'role' => 'player'
        ]);

        $newcomer = User::factory()->create([
            'name' => 'Newcomer Player',
            'email' => 'newcomer@test.com',
            'role' => 'player'
        ]);

        $this->playerToken = $player->createToken('PlayerToken')->plainTextToken;
        $this->newcomerToken = $newcomer->createToken('NewcomerToken')->plainTextToken;

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
        ])->putJson("/api/players/{$playerId}", [
                'name' => 'Updated name',
                'nickname' => 'Updated nickname'
        ]);

        $response->assertStatus(200)
                ->assertJson(['message' => 'User data updated successfully.']);
    }

    public function test_update_fail_existent_nickname() : void
    {
        $playerId = 2;
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->newcomerToken,
        ])->putJson("/api/players/{$playerId}", [
                'name' => 'Updated name',
                'nickname' => 'uniqueNickname'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['nickname']);
    }

    public function test_update_fail_unauthorized_access() : void
    {
        $playerId = 1;
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->wrongToken,
        ])->putJson("/api/players/{$playerId}");

        $response->assertStatus(401)
                ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_update_fail_forbidden_access() : void
    {
        $playerId = 1;
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->newcomerToken,
        ])->putJson("/api/players/{$playerId}");

        $response->assertStatus(403)
                ->assertJson(['message' => 'Forbidden.']);
    }

    public function test_play_pass() : void
    {
        $playerId = 1;
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->playerToken,
        ])->postJson("/api/players/{$playerId}/games");

        $response->assertStatus(201)
                ->assertJson(['message' => 'Play recorded successfully.'])
                ->assertJsonStructure([
                    'message',
                    'play' => [
                        'user_id',
                        'dice1',
                        'dice2',
                        'success',
                    ]                    
                ]);
    }

    public function test_play_fail_unauthorized_access() : void
    {
        $playerId = 1;
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->wrongToken,
        ])->postJson("/api/players/{$playerId}/games");

        $response->assertStatus(401)
                ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_play_fail_forbidden_access() : void
    {
        $playerId = 1;
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->newcomerToken,
        ])->postJson("/api/players/{$playerId}/games");

        $response->assertStatus(403)
            ->assertJson(['message' => 'Forbidden.']);
    }

    public function test_history_pass() : void
    {
        $playerId = 1;
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->playerToken,
        ])->getJson("/api/players/{$playerId}/games");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'plays'
            ])
            ->assertJson([
                'message' => 'All plays for user uniqueNickname',
                'plays' => [
                    ['user_id' => 1, 'dice1' => 1, 'dice2' => 2, 'success' => 0],
                    ['user_id' => 1, 'dice1' => 2, 'dice2' => 4, 'success' => 0],
                    ['user_id' => 1, 'dice1' => 1, 'dice2' => 3, 'success' => 0],
                    ['user_id' => 1, 'dice1' => 2, 'dice2' => 2, 'success' => 0],
                    ['user_id' => 1, 'dice1' => 3, 'dice2' => 4, 'success' => 1], // success
                    ['user_id' => 1, 'dice1' => 1, 'dice2' => 1, 'success' => 0],
                    ['user_id' => 1, 'dice1' => 2, 'dice2' => 3, 'success' => 0],
                    ['user_id' => 1, 'dice1' => 4, 'dice2' => 5, 'success' => 0],
                    ['user_id' => 1, 'dice1' => 5, 'dice2' => 4, 'success' => 0],
                    ['user_id' => 1, 'dice1' => 1, 'dice2' => 3, 'success' => 0],
                    ['user_id' => 1, 'dice1' => 2, 'dice2' => 4, 'success' => 0],
                    ['user_id' => 1, 'dice1' => 6, 'dice2' => 1, 'success' => 1], // success
                    ['user_id' => 1, 'dice1' => 2, 'dice2' => 5, 'success' => 1], // success
                    ['user_id' => 1, 'dice1' => 3, 'dice2' => 4, 'success' => 1], // success
                    ['user_id' => 1, 'dice1' => 2, 'dice2' => 2, 'success' => 0],
                    ['user_id' => 1, 'dice1' => 1, 'dice2' => 1, 'success' => 0],
                ]
            ]);
    }

    public function test_history_fail_unauthorized_access() : void
    {
        $playerId = 1;
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->wrongToken,
        ])->getJson("/api/players/{$playerId}/games");

        $response->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_history_fail_forbidden_access() : void
    {
        $playerId = 1;
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->newcomerToken,
        ])->getJson("/api/players/{$playerId}/games");

        $response->assertStatus(403)
                ->assertJson(['message' => 'Forbidden.']);
    }

    public function test_destroy_pass() : void
    {
        $playerId = 1;
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->playerToken,
        ])->deleteJson("/api/players/{$playerId}/games");

        $response->assertStatus(200)
            ->assertJson(['message' => 'All plays for user uniqueNickname have been deleted.']);
    }

    public function test_destroy_fail_unauthorized_access() : void
    {
        $playerId = 1;
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->wrongToken,
        ])->deleteJson("/api/players/{$playerId}/games");

        $response->assertStatus(401)
                ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_destroy_fail_forbidden_access() : void
    {
        $playerId = 1;
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->newcomerToken,
        ])->deleteJson("/api/players/{$playerId}/games");

        $response->assertStatus(403)
                ->assertJson(['message' => 'Forbidden.']);
    }
}
