<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // If the user is just a customer, we return the customer dashboard view (or default)
        // For now, everyone uses the same view, but we can pass different data.

        $stats = null;

        if ($user->hasRole('admin') || $user->hasRole('manager')) {
            $stats = [
                'total_revenue' => Order::whereIn('status', ['paid', 'processing', 'shipped', 'delivered'])->sum('grand_total'),
                'total_orders' => Order::count(),
                'pending_orders' => Order::whereIn('status', ['pending', 'processing'])->count(),
                'active_products' => Product::where('is_active', true)->count(),
            ];
        }

        return Inertia::render('Dashboard', [
            'stats' => $stats,
        ]);
    }
}
