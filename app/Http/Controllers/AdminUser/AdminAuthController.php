<?php

namespace App\Http\Controllers\AdminUser;

use App\Http\Requests\UpdateAdminProfileRequest;
use App\Http\Requests\UpdateAdminUserRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminLoginRequest;
use App\Http\Requests\AdminStoreRequest;
use App\Http\Resources\AdminUserResource;
use App\Models\AdminUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminAuthController extends Controller
{
    public function login(AdminLoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        $user = AdminUser::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'Email atau password salah.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $token = $user->createToken('admin-auth-token-' . $user->id)->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil.',
            'user'    => new AdminUserResource($user),
            'token'   => $token,
        ], Response::HTTP_OK); // 200 OK
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user('admin_users')->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil.',
        ], Response::HTTP_OK);
    }

    public function profile(Request $request): JsonResponse
    {
        return response()->json(new AdminUserResource($request->user('admin_users')), Response::HTTP_OK);
    }

    public function updateProfile(UpdateAdminProfileRequest $request): JsonResponse
    {
        $user = $request->user('admin_users');
        $validatedData = $request->validated();

        if (empty($validatedData['password'])) {
            unset($validatedData['password']);
        }

        $user->update($validatedData);

        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
            'user'    => new AdminUserResource($user->fresh()),
        ], Response::HTTP_OK);
    }
}
