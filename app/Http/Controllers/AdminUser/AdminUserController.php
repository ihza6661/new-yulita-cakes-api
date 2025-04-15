<?php

namespace App\Http\Controllers\AdminUser;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminStoreRequest;
use App\Http\Requests\AdminUserUpdateRequest;
use App\Http\Resources\AdminUserResource;
use App\Models\AdminUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminUserController extends Controller
{
    public function index(): JsonResponse
    {
        $admins = AdminUser::orderBy('name', 'asc')->get();
        return AdminUserResource::collection($admins)
                ->response()
                ->setStatusCode(Response::HTTP_OK);
    }

    public function store(AdminStoreRequest $request): JsonResponse
    {
        $admin = AdminUser::create($request->validated());

        return response()->json([
             'message' => 'Admin baru berhasil dibuat.',
             'user' => new AdminUserResource($admin),
        ], Response::HTTP_CREATED);
    }

    public function show(AdminUser $adminUser): JsonResponse
    {
        return response()->json(new AdminUserResource($adminUser), Response::HTTP_OK);
    }

    public function update(AdminUserUpdateRequest $request, AdminUser $adminUser): JsonResponse
    {
        $adminUser->update($request->validated());

        return response()->json([
            'message' => 'Data admin berhasil diperbarui.',
            'user'    => new AdminUserResource($adminUser->fresh()),
        ], Response::HTTP_OK);
    }

    public function destroy(Request $request, AdminUser $adminUser): JsonResponse
    {
        if ($request->user('admin_users')->id === $adminUser->id) {
            return response()->json([
                'message' => 'Anda tidak dapat menghapus akun sendiri.'
            ], Response::HTTP_FORBIDDEN);
        }

        $adminUser->delete();

        return response()->json([
            'message' => 'Admin berhasil dihapus.'
        ], Response::HTTP_OK);
    }
}
