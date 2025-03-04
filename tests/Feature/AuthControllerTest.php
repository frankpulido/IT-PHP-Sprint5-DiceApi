<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase; // Automatically migrates the test database

    public function test_register_creates_new_player_anonymous() : void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test Player Anonymous',
            'email' => 'test_anonymous@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(201)
                ->assertJsonStructure(['message', 'player', 'your_token'])
                ->assertJson(['message' => 'Player created successfully.']);
    }

    public function test_register_creates_new_player_nickname() : void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test Player Nickname',
            'nickname' => 'nickname',
            'email' => 'test_nickname@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(201)
                ->assertJsonStructure(['message', 'player', 'your_token'])
                ->assertJson(['message' => 'Player created successfully.']);
    }

    public function test_register_fails_existent_nickname()
    {
        $this->postJson('/api/register', [
            'name' => 'Test Player Nickname',
            'nickname' => 'nickname',
            'email' => 'test_nickname@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response = $this->postJson('/api/register', [
            'name' => 'Duplicate Nickname Player',
            'nickname' => 'nickname', // same nickname
            'email' => 'duplicate_nickname@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['nickname'])
                ->assertJson(['message' => 'The nickname must be unique unless it is "anonymous".']);
    }

    public function test_register_fails_existent_email()
    {
        $this->postJson('/api/register', [
            'name' => 'Test Player Anonymous',
            'email' => 'test_anonymous@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response = $this->postJson('/api/register', [
            'name' => 'Duplicate Email Player',
            'email' => 'test_anonymous@test.com', // same email
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
    }

    public function test_login_successful()
    {
        $this->postJson('/api/register', [
            'name' => 'Test Player Anonymous',
            'email' => 'test_anonymous@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test_anonymous@test.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure(['message', 'player', 'your_token'])
                ->assertJson(['message' => 'User successfully logged in.']);
    }

    public function test_login_fails_wrong_email()
    {
        $this->postJson('/api/register', [
            'name' => 'Test Player Anonymous',
            'email' => 'test_anonymous@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'wrongemail@test.com', // wrong email
            'password' => 'password',
        ]);

        $response->assertStatus(422)
                ->assertJson(['message' => 'The selected email is invalid.']);
    }

    public function test_login_fails_wrong_password()
    {
        $this->postJson('/api/register', [
            'name' => 'Test Player Anonymous',
            'email' => 'test_anonymous@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test_anonymous@test.com',
            'password' => 'wrongpassword', // wrong password
        ]);

        $response->assertStatus(401)
                ->assertJson(['message' => 'Incorrect password. Try again.']);
    }

    public function test_logout_successful() : void
    {
        $response1 = $this->postJson('/api/register', [
            'name' => 'Test Player Anonymous',
            'email' => 'test_anonymous@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $token = $response1['your_token'];

        $response2 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/logout');
    
        $response2->assertStatus(200)
                  ->assertJson(['message' => 'User successfully logged out.']);
    }

    public function test_fails_invalid_token() : void
    {
        $token = "invalid_token";
        $response2 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/logout');
    
        $response2->assertStatus(401)
                  ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_fails_no_token() : void
    {
        $response2 = $this->postJson('/api/logout');
    
        $response2->assertStatus(401)
                  ->assertJson(['message' => 'Unauthenticated.']);
    }
}
