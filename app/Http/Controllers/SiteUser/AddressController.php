<?php

namespace App\Http\Controllers\SiteUser;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\SiteUser\AddressRequest;
use App\Http\Resources\SiteUser\AddressResource;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class AddressController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $addresses = $request->user()
            ->addresses()
            ->orderByDesc('is_default')
            ->latest()
            ->get();

        return AddressResource::collection($addresses);
    }

    public function store(AddressRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $address = DB::transaction(function () use ($user, $validated, $request) {
            if ($request->boolean('is_default')) {
                $user->addresses()->update(['is_default' => false]);
            }
            return $user->addresses()->create($validated);
        });

        return response()->json([
            'message' => 'Alamat berhasil ditambahkan.',
            'address' => new AddressResource($address),
        ], 201);
    }

    public function show(Request $request, Address $address): AddressResource|JsonResponse
    {
        if ($request->user()->id !== $address->site_user_id) {
            return response()->json(['message' => 'Akses ditolak.'], 403); // 403 Forbidden
        }

        return new AddressResource($address);
    }

    public function update(AddressRequest $request, Address $address): JsonResponse
    {
        if ($request->user()->id !== $address->site_user_id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $validated = $request->validated();

        DB::transaction(function () use ($request, $address, $validated) {
            if ($request->boolean('is_default')) {
                $request->user()->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
            }
            $address->update($validated);
        });

        return response()->json([
            'message' => 'Alamat berhasil diperbarui.',
            'address' => new AddressResource($address->refresh()),
        ], 200);
    }

    public function destroy(Request $request, Address $address): JsonResponse
    {
        if ($request->user()->id !== $address->site_user_id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }
        // ============================

        if ($address->is_default) {
            $otherAddress = $request->user()->addresses()->where('id', '!=', $address->id)->first();
            if ($otherAddress) {
                $otherAddress->update(['is_default' => true]);
            }
        }

        $address->delete();

        return response()->json(null, 204); // 204 No Content
    }

    public function setDefault(Request $request, Address $address): JsonResponse
    {
        if ($request->user()->id !== $address->site_user_id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }
        // ============================

        DB::transaction(function () use ($request, $address) {
            $request->user()->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
            $address->update(['is_default' => true]);
        });

        return response()->json([
            'message' => 'Alamat default berhasil diperbarui.',
            'address' => new AddressResource($address->refresh()),
        ], 200);
    }
}
