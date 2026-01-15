<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Http\Requests\User\UserRequest;
use App\Http\Requests\User\UserUpdateRequest;
use App\Http\Services\User\UserService;
use App\Http\Resources\User\UserResource;
use App\Exceptions\NotFoundException;

class UserController extends Controller
{
    protected $userService;

    /**
     * UserController constructor.
     *
     * @param \App\Services\User\UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Get list of all users.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $conditions = $request->all();

        $users = $this->userService->getByConditions($conditions);

        return response()->json([
            'data' => UserResource::collection($users)
        ]);
    }

    /**
     * Store a newly created user.
     *
     * @param  \App\Http\Requests\User\UserRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(UserRequest $request): JsonResponse
    {
        $user = $this->userService->create($request->validated());

        return response()->json([
            'message' => 'User created successfully.',
            'data' => new UserResource($user)
        ], 201);
    }

    /**
     * Display the specified user.
     *
     * @param \App\Models\User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(User $user): JsonResponse
    {
        $conditions['filter']['eq'] = [
            'id' => $user->id,
        ];

        $user = $this->userService->findByConditions($conditions);

        if (!$user) {
            throw new NotFoundException("User not found.");
        }

        return response()->json([
            'data' => new UserResource($user)
        ]);
    }
    /**
     * Update the specified user.
     *
     * @param \App\Http\Requests\UserUpdateRequest $request
     * @param \App\Models\User $user
     * @return JsonResponse
     */
    public function update(UserUpdateRequest $request, User $user)
    {
        $updatedUser = $this->userService->update(
            $request->validated(),
            $user,
        );
        return response()->json([
            'data' => new UserResource($updatedUser)
        ]);
    }

    /**
     * Remove the specified user.
     *
     * @param \App\Models\User $user
     * @return JsonResponse
     */
    public function destroy(User $user)
    {
        $this->userService->destroy($user);

        return response()->json([
            'message' => 'User deleted successfully!'
        ]);
    }
}
