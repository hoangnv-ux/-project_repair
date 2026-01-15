<?php

namespace App\Repositories\Eloquent\User;

use App\Models\User;
use App\Repositories\Contracts\User\UserRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    /**
    * Create a new class instance.
    */
    public function __construct(User $model)
    {
        parent::__construct($model);
    }
}
