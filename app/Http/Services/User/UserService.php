<?php

namespace App\Http\Services\User;

use App\Models\User;
use App\Http\Services\BaseService;
use Illuminate\Support\Facades\Hash;
use App\Exceptions\NotFoundException;

class UserService extends BaseService
{
    /**
     * Create a new class instance.
     */
    public function __construct(User $model)
    {
        $this->model = $model;
    }

    /**
     * Create a new user.
     *
     * @param array $data
     * @return \App\Models\User
     */
    public function create(array $data): User
    {
        $data['password'] = Hash::make($data['password']);
        return parent::store($data);
    }

    /**
     * Update an existing user.
     *
     * @param array $data
     * @param User  $user
     * @return User
     *
     * @throws \App\Exceptions\NotFoundException
     */
    public function update($data, $user): User
    {
        if (!$user) {
            $user = $this->model->find($data['id']);
            if (!$user) {
                throw new NotFoundException("User not found!");
            }
        }

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        parent::update($data, $user);
        return $this->loadRelationship()->where('id', $user->id)->first();
    }

    /**
     * Delete a user.
     *
     * @param \App\Models\User $user
     * @return \App\Models\User
     * @throws \App\Exceptions\NotFoundException
     */
    public function destroy($user): User
    {
        return parent::destroy($user);
    }
}
