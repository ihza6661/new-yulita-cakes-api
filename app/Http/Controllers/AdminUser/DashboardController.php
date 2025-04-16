<?php

namespace App\Http\Controllers\AdminUser;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Product;
use App\Models\SiteUser;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DashboardController extends Controller
{
    public function summary(): JsonResponse
    {
        $validSaleStatuses = [
            OrderStatus::PAID->value,
            OrderStatus::PROCESSING->value,
            OrderStatus::SHIPPED->value,
            OrderStatus::DELIVERED->value,
        ];

        $countableOrderStatuses = [
            OrderStatus::PENDING->value,
            OrderStatus::PAID->value,
            OrderStatus::PROCESSING->value,
            OrderStatus::SHIPPED->value,
            OrderStatus::DELIVERED->value,
        ];

        $totalSales = Order::whereIn('status', $validSaleStatuses)->sum('total_amount');

        $totalOrders = Order::whereIn('status', $countableOrderStatuses)->count();

        $totalUsers = SiteUser::where('is_active', true)->count();

        $totalProducts = Product::count();

        return response()->json([
            'totalSales'    => (float) $totalSales, // Cast ke float
            'totalOrders'   => (int) $totalOrders,
            'totalUsers'    => (int) $totalUsers,
            'totalProducts' => (int) $totalProducts,
        ]);
    }

    public function ordersData(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date|date_format:Y-m-d',
            'end_date'   => 'nullable|date|date_format:Y-m-d|after_or_equal:start_date',
        ]);

        $endDate = isset($validated['end_date']) ? Carbon::parse($validated['end_date'])->endOfDay() : Carbon::now()->endOfDay();
        $startDate = isset($validated['start_date']) ? Carbon::parse($validated['start_date'])->startOfDay() : $endDate->copy()->subMonths(11)->startOfMonth(); // Default 12 bulan

        $countableOrderStatuses = [
            OrderStatus::PENDING->value,
            OrderStatus::PAID->value,
            OrderStatus::PROCESSING->value,
            OrderStatus::SHIPPED->value,
            OrderStatus::DELIVERED->value,
        ];

        $ordersQuery = Order::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count')
            ->whereIn('status', $countableOrderStatuses) // Filter status
            ->whereBetween('created_at', [$startDate, $endDate]) // Filter tanggal
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        $reportData = $this->padChartData($ordersQuery, $startDate, $endDate, 'count');

        return response()->json($reportData);
    }

    public function salesData(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date|date_format:Y-m-d',
            'end_date'   => 'nullable|date|date_format:Y-m-d|after_or_equal:start_date',
        ]);

        $endDate = isset($validated['end_date']) ? Carbon::parse($validated['end_date'])->endOfDay() : Carbon::now()->endOfDay();
        $startDate = isset($validated['start_date']) ? Carbon::parse($validated['start_date'])->startOfDay() : $endDate->copy()->subMonths(11)->startOfMonth();

        $validSaleStatuses = [
            OrderStatus::PAID->value,
            OrderStatus::PROCESSING->value,
            OrderStatus::SHIPPED->value,
            OrderStatus::DELIVERED->value,
        ];

        $salesQuery = Order::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, SUM(total_amount) as total')
            ->whereIn('status', $validSaleStatuses) // Filter status penjualan
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        $reportData = $this->padChartData($salesQuery, $startDate, $endDate, 'total', true); // Cast ke float

        return response()->json($reportData);
    }

    public function recentOrders(): AnonymousResourceCollection
    {
        $displayableOrderStatuses = [
            OrderStatus::PAID->value,
            OrderStatus::PROCESSING->value,
            OrderStatus::SHIPPED->value,
            OrderStatus::DELIVERED->value,
        ];

        $orders = Order::with([
            'user:id,name',
            'payment:order_id,status',
            'shipment:order_id,status'
        ])
            ->whereIn('status', $displayableOrderStatuses)
            ->latest() // orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return OrderResource::collection($orders);
    }

    protected function padChartData($queryResult, Carbon $startDate, Carbon $endDate, string $valueKey, bool $toFloat = false): array
    {
        $resultGrouped = $queryResult->keyBy(function ($item) {
            return sprintf('%d-%02d', $item->year, $item->month);
        });

        $paddedData = [];
        $currentMonth = $startDate->copy()->startOfMonth();

        while ($currentMonth <= $endDate) {
            $key = $currentMonth->format('Y-m');
            $monthName = $currentMonth->translatedFormat('M Y');

            $value = $resultGrouped->has($key)
                ? ($toFloat ? (float)$resultGrouped[$key]->$valueKey : (int)$resultGrouped[$key]->$valueKey)
                : 0;

            $paddedData[] = [
                'name' => $monthName,
                $valueKey => $value,
            ];

            $currentMonth->addMonthNoOverflow();
        }

        return $paddedData;
    }
}
