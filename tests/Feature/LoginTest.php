<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LoginTest extends TestCase
{
    public function test_login_valid_credentials(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);

        $response->assertOk();

        $response->assertJsonStructure([
            'user' => [
                'name',
                'email',
            ],
            'access_token',
        ]);
    }
    public function test_login_invalid_password(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'admin@test.com',
            'password' => 'passwordIncorrecto',
        ]);

        $response->assertJson([
            'message' => 'Credenciales Incorrectas',
        ]);
    }
    public function test_login_user_not_found(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'noexiste@test.com',
            'password' => 'password',
        ]);

        $response->assertJson([
            'message' => 'Credenciales Incorrectas',
        ]);
    }
    public function test_login_validation_required_fields(): void
    {
        $response = $this->postJson('/api/login', []);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'email',
            'password',
        ]);
    }
    public function test_login_invalid_email_format(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'correo-invalido',
            'password' => 'password',
        ]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'email',
        ]);
    }
    public function test_login_password_required(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'admin@test.com',
            'password' => '',
        ]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'password',
        ]);
    }
    public function test_login_email_required(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => '',
            'password' => 'password',
        ]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'email',
        ]);
    }
}
