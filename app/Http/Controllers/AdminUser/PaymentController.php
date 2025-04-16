<?php

namespace App\Http\Controllers\AdminUser;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PaymentController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $payments = Payment::with([
            'order:id,order_number,site_user_id,status,created_at',
            'order.user:id,name,email'
        ])
            ->orderBy('created_at', 'desc')
            ->get();

        return PaymentResource::collection($payments);
    }

    public function show(Payment $payment): PaymentResource
    {
        $payment->load(['order.user:id,name,email']);

        return new PaymentResource($payment);
    }
}
