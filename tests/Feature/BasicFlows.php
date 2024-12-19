<?php

namespace Tests\Feature;

use App\Mail\ConfirmEmail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BasicFlows extends TestCase
{

    // SIGNUP
    public function it_registers_a_user_correctly()
    {
        Storage::fake('local'); // Fake para simular el almacenamiento de archivos
        Mail::fake();           // Fake para simular el envío de correos
    
        $data = [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'id_photo' => UploadedFile::fake()->image('id.jpg'),
        ];
    
        $response = $this->postJson('/api/signup', $data);
    
        $response->assertStatus(204);
        Storage::assertExists("ids/{$data['id_photo']->hashName()}");
        $this->assertDatabaseHas('users', ['email' => $data['email']]);
        Mail::assertQueued(ConfirmEmail::class);
    }

    // LOGIN
    public function it_logs_in_a_user_correctly()
    {
        $user = User::factory()->create([
            'email' => 'john.doe@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'john.doe@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure(['user_data', 'token']);
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function it_fails_to_log_in_with_invalid_credentials()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'invalid@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
        $this->assertGuest();
    }

    // LOOGOUT
    public function it_logs_out_a_user()
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        $this->actingAs($user)->postJson('/api/auth/logout');

        $this->assertDatabaseMissing('personal_access_tokens', ['token' => hash('sha256', $token)]);
        $this->assertGuest();
    }

    // GET authed data
    public function it_returns_authenticated_user_data()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->getJson('/api/auth/protected-resource')
            ->assertStatus(200)
            ->assertJsonFragment([
                'status' => 'employed',
                'hopefully' => 'please :\')',
                'user' => ['id' => $user->id],
            ]);
    }
}
