<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\Admin;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected array $authHeader;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = Admin::factory()->create();
        $token = JWTAuth::fromUser($admin);

        $this->authHeader = [
            'Authorization' => "Bearer {$token}",
        ];
    }

    public function test_can_create_admin()
    {
        $data = [
            'name' => 'New Admin',
            'email' => 'newadmin@example.com',
            'password' => 'secret123',
        ];

        $response = $this->postJson('/api/admin', $data, $this->authHeader);

        $response->assertStatus(201)->assertJsonFragment(['email' => 'newadmin@example.com']);

        $this->assertDatabaseHas('admins', ['email' => 'newadmin@example.com']);
    }

    public function test_can_get_all_admins()
    {
        Admin::factory()->count(3)->create();

        $response = $this->getJson('/api/admin', $this->authHeader);

        $response->assertStatus(200);
        $response->assertJsonStructure(['data']);
    }

    public function test_can_get_single_admin()
    {
        $admin = Admin::factory()->create();

        $response = $this->getJson("/api/admin/{$admin->id}", $this->authHeader);

        $response->assertStatus(200)->assertJsonFragment(['id' => $admin->id]);
    }

    public function test_can_update_admin()
    {
        $admin = Admin::factory()->create();

        $response = $this->putJson("/api/admin/{$admin->id}", [
            'name' => 'Updated Name',
        ], $this->authHeader);

        $response->assertStatus(200);
        $this->assertDatabaseHas('admins', ['id' => $admin->id, 'name' => 'Updated Name']);
    }

    public function test_can_delete_admin()
    {
        $admin = Admin::factory()->create();

        $response = $this->deleteJson("/api/admin/{$admin->id}", [], $this->authHeader);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('admins', ['id' => $admin->id]);
    }

    public function test_create_admin_should_fail_with_missing_fields()
    {
        $response = $this->postJson('/api/admin', [
            'name' => 'No Email',
        ], $this->authHeader);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_create_admin_should_fail_with_duplicate_email()
    {
        $existingAdmin = Admin::factory()->create([
            'email' => 'duplicate@example.com',
        ]);

        $response = $this->postJson('/api/admin', [
            'name' => 'Dup Name',
            'email' => 'duplicate@example.com',
            'password' => 'secret123',
        ], $this->authHeader);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_get_admin_should_fail_if_not_found()
    {
        $response = $this->getJson('/api/admin/99999', $this->authHeader);

        $response->assertStatus(404);
    }

    public function test_update_admin_should_fail_if_not_found()
    {
        $response = $this->putJson('/api/admin/99999', [
            'name' => 'Nonexistent Update',
        ], $this->authHeader);

        $response->assertStatus(404);
    }

    public function test_delete_admin_should_fail_if_not_found()
    {
        $response = $this->deleteJson('/api/admin/99999', [], $this->authHeader);

        $response->assertStatus(404);
    }

    public function test_create_admin_should_fail_without_authentication()
    {
        $response = $this->postJson('/api/admin', [
            'name' => 'No Auth',
            'email' => 'noauth@example.com',
            'password' => 'secret123',
        ]);

        $response->assertStatus(401);
    }
}
