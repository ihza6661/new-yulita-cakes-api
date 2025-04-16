<?php

namespace App\Http\Controllers\AdminUser;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSiteUserStatusRequest;
use App\Http\Resources\SiteUserResource;
use App\Models\SiteUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class SiteUserController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $users = SiteUser::orderBy('name', 'asc')->get();
        return SiteUserResource::collection($users);
    }

    public function show(SiteUser $siteUser): SiteUserResource
    {
        $siteUser->load(['addresses', 'orders' => function ($query) {
            $query->with(['payment', 'shipment'])
                ->orderBy('created_at', 'desc');
        }]);

        return new SiteUserResource($siteUser);
    }

    public function updateStatus(UpdateSiteUserStatusRequest $request, SiteUser $siteUser): JsonResponse
    {
        $validatedData = $request->validated();

        $siteUser->update($validatedData);

        return response()->json([
            'message' => 'Status akun pengguna berhasil diperbarui.',
            'user'    => new SiteUserResource($siteUser)
        ], Response::HTTP_OK);
    }
}
