<?php

namespace Tests\Unit\Admin\Admin;

use Tests\TestCase;
use App\Http\Services\Admin\AdminService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;

class AdminServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AdminService $adminService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminService = $this->app->make(AdminService::class);
    }

    public function test_it_can_get_all_admins()
    {
        Admin::factory()->count(3)->create();

        $results = $this->adminService->getByConditions(['per_page' => -1]);

        $this->assertCount(3, $results->items());
    }

    public function test_it_can_find_single_admin_by_email()
    {
        $admin = Admin::factory()->create(['email' => 'unique@example.com']);

        $result = $this->adminService->findByConditions([
            'filter' => ['eq' => ['email' => 'unique@example.com']]
        ]);

        $this->assertNotNull($result);
        $this->assertEquals($admin->id, $result->id);
    }

    public function test_it_returns_null_when_admin_does_not_exist()
    {
        $result = $this->adminService->findByConditions([
            'filter' => ['eq' => ['email' => 'nonexistent@example.com']]
        ]);

        $this->assertNull($result);
    }

    public function test_it_can_create_an_admin()
    {
        $data = [
            'name' => 'New Admin',
            'email' => 'newadmin@example.com',
            'password' => 'secret123'
        ];

        $admin = $this->adminService->create($data);

        $this->assertInstanceOf(Admin::class, $admin);
        $this->assertEquals('newadmin@example.com', $admin->email);
        $this->assertTrue(Hash::check('secret123', $admin->password));
    }

    public function test_it_can_update_an_admin()
    {
        $admin = Admin::factory()->create([
            'name' => 'Old Name',
            'password' => bcrypt('oldpass')
        ]);

        $data = [
            'name' => 'Updated Name',
            'password' => 'newpass'
        ];

        $updated = $this->adminService->update($data, $admin);

        $this->assertEquals('Updated Name', $updated->name);
        $this->assertTrue(Hash::check('newpass', $updated->password));
    }

    public function test_it_can_destroy_an_admin()
    {
        $admin = Admin::factory()->create();

        $deleted = $this->adminService->destroy($admin);

        $this->assertEquals($admin->id, $deleted->id);
        $this->assertDatabaseMissing('admins', ['id' => $admin->id]);
    }
}
