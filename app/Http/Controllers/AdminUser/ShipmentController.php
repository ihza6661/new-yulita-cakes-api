<?php

namespace App\Http\Controllers\AdminUser;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateShipmentRequest;
use App\Http\Resources\ShipmentResource;
use App\Models\Shipment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class ShipmentController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $shipments = Shipment::with([
            'order:id,order_number,site_user_id,status,created_at',
            'order.user:id,name',
            'order.payment:order_id,status'
        ])
            ->orderBy('created_at', 'desc')
            ->get();

        return ShipmentResource::collection($shipments);
    }

    public function show(Shipment $shipment): ShipmentResource
    {
        $shipment->load([
            'order',
            'order.user:id,name,email',
            'order.payment'
        ]);

        return new ShipmentResource($shipment);
    }

    public function update(UpdateShipmentRequest $request, Shipment $shipment): JsonResponse
    {
        $validatedData = $request->validated();

        if (!empty($validatedData)) {
            $shipment->update($validatedData);
        }

        $shipment->load(['order.user:id,name', 'order.payment']);

        return response()->json([
            'message' => 'Data pengiriman berhasil diperbarui.',
            'shipment' => new ShipmentResource($shipment)
        ], Response::HTTP_OK);
    }
}
