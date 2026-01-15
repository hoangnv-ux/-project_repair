<?php

namespace Tests\Unit\Admin\Admin;

use Tests\TestCase;
use App\Models\Admin;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Admin\AdminStoreRequest;
use App\Http\Requests\Admin\AdminUpdateRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function validate(string $requestClass, array $data, array $routeParams = [])
    {
        $request = new $requestClass();

        if (!empty($routeParams)) {
            $mockRoute = new class($routeParams) {
                private $parameters;

                public function __construct(array $parameters)
                {
                    $this->parameters = $parameters;
                }

                public function parameter($key)
                {
                    return $this->parameters[$key] ?? null;
                }
            };

            $request->setRouteResolver(function () use ($mockRoute) {
                return $mockRoute;
            });
        }

        $rules = $request->rules();
        $messages = method_exists($request, 'messages') ? $request->messages() : [];

        return Validator::make($data, $rules, $messages);
    }

    // =================================
    // STORE REQUEST TESTS
    // =================================
    public function test_it_validates_admin_store_request_successfully()
    {
        $validator = $this->validate(AdminStoreRequest::class, [
            'name'     => 'John Doe',
            'email'    => 'john@example.com',
            'password' => 'secret123',
        ]);

        $this->assertFalse($validator->fails());
    }

    public function test_it_fails_admin_store_request_when_required_fields_missing()
    {
        $validator = $this->validate(AdminStoreRequest::class, []);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    public function test_it_fails_admin_store_request_with_invalid_email_format()
    {
        $validator = $this->validate(AdminStoreRequest::class, [
            'name'     => 'John Doe',
            'email'    => 'invalid-email',
            'password' => 'secret123',
        ]);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    public function test_it_fails_admin_store_request_with_duplicate_email()
    {
        Admin::factory()->create([
            'email' => 'john@example.com',
        ]);

        $validator = $this->validate(AdminStoreRequest::class, [
            'name'     => 'New Admin',
            'email'    => 'john@example.com',
            'password' => 'secret123',
        ]);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    // =================================
    // UPDATE REQUEST TESTS
    // =================================
    public function test_it_validates_admin_update_request_successfully()
    {
        $admin = Admin::factory()->create();

        $validator = $this->validate(AdminUpdateRequest::class, [
            'name'  => 'Updated Name',
            'email' => 'updated@example.com',
        ], [
            'admin' => $admin,
        ]);

        $this->assertFalse($validator->fails());
    }

    public function test_it_fails_admin_update_request_with_invalid_email()
    {
        $admin = Admin::factory()->create();

        $validator = $this->validate(AdminUpdateRequest::class, [
            'email' => 'not-an-email',
        ], [
            'admin' => $admin,
        ]);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    public function test_it_fails_admin_update_request_with_duplicate_email()
    {
        $existingAdmin = Admin::factory()->create([
            'email' => 'existing@example.com',
        ]);

        $admin = Admin::factory()->create();

        $validator = $this->validate(AdminUpdateRequest::class, [
            'email' => 'existing@example.com',
        ], [
            'admin' => $admin,
        ]);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    public function test_it_allows_same_email_for_self_in_update()
    {
        $admin = Admin::factory()->create([
            'email' => 'self@example.com',
        ]);

        $validator = $this->validate(AdminUpdateRequest::class, [
            'email' => 'self@example.com',
        ], [
            'admin' => $admin,
        ]);

        $this->assertFalse($validator->fails());
    }
}
