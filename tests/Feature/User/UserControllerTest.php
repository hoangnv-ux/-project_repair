<?php

namespace Tests\Feature\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user listing (GET /api/user)
     *
     * @return void
     */
    public function test_can_list_users(): void
    {
        User::factory()->count(5)->create();

        $response = $this->getJson('/api/user');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [['id', 'name', 'email']]
                 ]);
    }

    /**
     * Test user creation (POST /api/user)
     *
     * @return void
     */
    public function test_can_create_user(): void
    {
        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123'
        ];

        $response = $this->postJson('/api/user', $data);

        $response->assertStatus(201)
                 ->assertJsonFragment([
                     'name' => 'Test User',
                     'email' => 'test@example.com',
                 ]);

        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    /**
     * Test user update (PUT /api/user/{id})
     *
     * @return void
     */
    public function test_can_update_user(): void
    {
        $user = User::factory()->create();

        $data = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ];

        $response = $this->putJson("/api/user/{$user->id}", $data);

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'Updated Name']);

        $this->assertDatabaseHas('users', ['email' => 'updated@example.com']);
    }

    /**
     * Test user deletion (DELETE /api/user/{id})
     *
     * @return void
     */
    public function test_can_delete_user(): void
    {
        $user = User::factory()->create();

        $response = $this->deleteJson("/api/user/{$user->id}");

        $response->assertStatus(200)
                 ->assertJson(['message' => 'User deleted successfully!']);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
