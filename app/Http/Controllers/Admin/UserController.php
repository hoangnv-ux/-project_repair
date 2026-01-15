<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTraits;
use App\Models\User;
use App\Exceptions\NotFoundException;
use Illuminate\Http\Request;
use App\Http\Services\Admin\UserService;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\User\UserResource;

class UserController extends Controller
{
    use ResponseTraits;

    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $conditions = $request->all();
        $users = $this->userService->getByConditions($conditions);
        $meta = $this->userService->getPagination($users);

        return $this->responseSuccess(
            status: Response::HTTP_OK,
            message: __('common.get_list_success'),
            data: UserResource::collection($users),
            meta: $meta,
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = $this->userService->store($request->all());

        return $this->responseSuccess(
            status: Response::HTTP_CREATED,
            message: __('common.create_success'),
            data: $user,
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $conditions['filter']['eq'] = [
            'id' => $user->id,
        ];
        $user = $this->userService->findByConditions($conditions);
        if (!$user) {
            throw new NotFoundException(
                __('common.not_found'),
                Response::HTTP_NOT_FOUND,
            );
        }

        return $this->responseSuccess(
            status: Response::HTTP_OK,
            message: __('common.get_success'),
            data: $user,
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $user = $this->userService->update(
            $request->all(),
            $user
        );

        return $this->responseSuccess(
            status: Response::HTTP_OK,
            message: __('common.update_success'),
            data: $user,
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $this->userService->destroy($user);

        return $this->responseSuccess(
            status: Response::HTTP_OK,
            message: __('common.delete_success'),
        );
    }
}
