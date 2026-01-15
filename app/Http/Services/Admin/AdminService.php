<?php

namespace App\Http\Services\Admin;

use App\Models\Admin;
use App\Http\Services\BaseService;
use Illuminate\Support\Facades\Hash;
use App\Repositories\Contracts\Admin\AdminRepositoryInterface;

class AdminService extends BaseService
{
    /**
     * Create a new class instance.
     */
    public function __construct(AdminRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    /**
     * Create a new admin.
     *
     * @param array $data
     * @return \App\Models\Admin
     */
    public function create(array $data): Admin
    {
        $currentAdmin = auth('admin')->user();
        if (!$currentAdmin || $currentAdmin->role !== Admin::ROLE_SYSTEM_ADMIN) {
            $data['role'] = Admin::ROLE_ADMIN;
        }
        $data['password'] = Hash::make($data['password']);
        return parent::store($data);
    }

    /**
     * Update an existing admin.
     *
     * @param array $data
     * @param Admin  $admin
     * @return Admin
     *
     * @throws \App\Exceptions\NotFoundException
     */
    public function update($data, $admin): Admin
    {
        $currentAdmin = auth('admin')->user();
        if (!$currentAdmin || $currentAdmin->role !== Admin::ROLE_SYSTEM_ADMIN) {
            $data['role'] = Admin::ROLE_ADMIN;
        }
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        parent::update($data, $admin);
        return $this->loadRelationship()->where('id', $admin->id)->first();
    }

    /**
     * Delete a admin.
     *
     * @param \App\Models\Admin $admin
     * @return \App\Models\Admin
     * @throws \App\Exceptions\NotFoundException
     */
    public function destroy($admin): Admin
    {
        return parent::destroy($admin);
    }
}
