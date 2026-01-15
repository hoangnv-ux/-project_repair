<?php

namespace App\Http\Services\Admin;

use App\Http\Services\BaseService;
use App\Repositories\Eloquent\User\UserRepository;

class UserService extends BaseService
{
    public function __construct(UserRepository $repository)
    {
        parent::__construct($repository);
    }
}
