<?php

namespace App\Http\Controllers\AdminUser;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\SalesReportResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date|date_format:Y-m-d',
            'end_date'   => 'nullable|date|date_format:Y-m-d|after_or_equal:start_date',
        ], [
            'start_date.date' => 'Format tanggal mulai tidak valid.',
            'start_date.date_format' => 'Format tanggal mulai harus YYYY-MM-DD.',
            'end_date.date' => 'Format tanggal akhir tidak valid.',
            'end_date.date_format' => 'Format tanggal akhir harus YYYY-MM-DD.',
            'end_date.after_or_equal' => 'Tanggal akhir harus setelah atau sama dengan tanggal mulai.',
        ]);

        $query = DB::table('orders as o')
            ->join('order_items as oi', 'o.id', '=', 'oi.order_id')
            ->join('products as p', 'oi.product_id', '=', 'p.id')
            ->join('site_users as u', 'o.site_user_id', '=', 'u.id')
            ->leftJoin('payments as pay', 'o.id', '=', 'pay.order_id')
            ->leftJoin('shipments as s', 'o.id', '=', 's.order_id')
            ->select(
                'o.order_number',
                'o.created_at as order_date',
                'u.name as customer_name',
                'p.product_name',
                'oi.qty as quantity',
                'oi.price as item_price',
                DB::raw('(oi.qty * oi.price) as item_subtotal'),
                'o.status as order_status',
                'pay.status as payment_status',
                's.tracking_number'
            )
            ->whereIn('o.status', [
                OrderStatus::PAID->value,
                OrderStatus::PROCESSING->value,
                OrderStatus::SHIPPED->value,
                OrderStatus::DELIVERED->value,
            ]);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($validated['start_date'])->startOfDay();
            $endDate = Carbon::parse($validated['end_date'])->endOfDay();
            $query->whereBetween('o.created_at', [$startDate, $endDate]);
        }

        $salesReports = $query->orderBy('o.created_at', 'desc')->get();

        return SalesReportResource::collection($salesReports);
    }
}
