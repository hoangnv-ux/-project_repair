<?php

namespace Tests\Unit\Admin\Admin;

use Tests\TestCase;
use App\Models\Admin;
use App\Repositories\Eloquent\Admin\AdminRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected AdminRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new AdminRepository(new Admin());
    }

    public function test_create_admin()
    {
        $data = [
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('secret'),
        ];

        $admin = $this->repository->create($data);

        $this->assertInstanceOf(Admin::class, $admin);
        $this->assertDatabaseHas('admins', ['email' => 'admin@example.com']);
    }

    public function test_all_admins()
    {
        Admin::factory()->count(3)->create();

        $admins = $this->repository->all();

        $this->assertCount(3, $admins);
    }

    public function test_find_admin()
    {
        $admin = Admin::factory()->create();

        $found = $this->repository->find($admin->id);

        $this->assertEquals($admin->id, $found->id);
    }

    public function test_update_admin()
    {
        $admin = Admin::factory()->create(['name' => 'Old Name']);

        $updated = $this->repository->update(['name' => 'New Name'], $admin->id);

        $this->assertEquals('New Name', $updated->name);
    }

    public function test_delete_admin()
    {
        $admin = Admin::factory()->create();

        $result = $this->repository->delete($admin->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('admins', ['id' => $admin->id]);
    }

    public function test_update_or_create_admin()
    {
        $data = [
            'email' => 'unique@example.com',
            'name' => 'Original Name',
            'password' => bcrypt('abc123'),
        ];

        // First: create
        $admin = $this->repository->updateOrCreate(['email' => 'unique@example.com'], $data);

        $this->assertDatabaseHas('admins', ['email' => 'unique@example.com']);

        // Then: update
        $updated = $this->repository->updateOrCreate(
            ['email' => 'unique@example.com'],
            ['name' => 'Updated Name']
        );

        $this->assertEquals('Updated Name', $updated->name);
    }
}
