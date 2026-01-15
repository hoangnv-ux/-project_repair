<?php

namespace App\Repositories\Eloquent\Admin;

use App\Models\Admin;
use App\Repositories\Contracts\Admin\AdminRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class AdminRepository extends BaseRepository implements AdminRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct(Admin $model)
    {
        parent::__construct($model);
    }
}
