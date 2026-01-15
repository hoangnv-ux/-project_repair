<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Admin;
use App\Http\Requests\Admin\AdminStoreRequest;
use App\Http\Requests\Admin\AdminUpdateRequest;
use App\Http\Services\Admin\AdminService;
use App\Http\Resources\Admin\AdminResource;
use App\Exceptions\NotFoundException;
use Symfony\Component\HttpFoundation\Response;

class AdminController extends Controller
{
    protected $adminService;

    /**
     * UserController constructor.
     *
     * @param \App\Services\Admin\AdminService $adminService
     */
    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $conditions = $request->all();
        $conditions['per_page'] = $conditions['per_page'] ?? 10;
        $authAdmin = auth('admin')->user();
        if ($authAdmin) {
            if ($authAdmin->role === Admin::ROLE_ADMIN) {
                $conditions['filter']['in']['role'] = [Admin::ROLE_ADMIN];
            } elseif ($authAdmin->role === Admin::ROLE_SYSTEM_ADMIN) {
                $conditions['filter']['in']['role'] = [Admin::ROLE_SYSTEM_ADMIN, Admin::ROLE_ADMIN];
            }
        }

        $admins = $this->adminService->getByConditions($conditions);
        // paginate
        $meta = $this->adminService->getPagination($admins);

        return $this->responseSuccess(
            status: Response::HTTP_OK,
            message: __('common.get_list_success'),
            data: AdminResource::collection($admins),
            meta: $meta,
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AdminStoreRequest $request): JsonResponse
    {
        $admin = $this->adminService->create($request->all());

        return response()->json([
            'message' => 'Admin created successfully.',
            'data' => new AdminResource($admin)
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Admin $admin): JsonResponse
    {
        $conditions['filter']['eq'] = [
            'id' => $admin->id,
        ];

        $admin = $this->adminService->findByConditions($conditions);

        if (!$admin) {
            throw new NotFoundException("Admin not found.");
        }

        return response()->json([
            'data' => new AdminResource($admin)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AdminUpdateRequest $request, Admin $admin)
    {
        if (!$admin) {
            throw new NotFoundException('Admin not found!');
        }
        $update_admin = $this->adminService->update(
            $request->all(),
            $admin,
        );
        return response()->json([
            'data' => new AdminResource($update_admin)
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Admin $admin)
    {
        if (!$admin) {
            throw new NotFoundException('Admin not found.');
        }

        $this->adminService->destroy($admin);

        return response()->json([
            'message' => 'Admin deleted successfully!'
        ]);
    }
}
