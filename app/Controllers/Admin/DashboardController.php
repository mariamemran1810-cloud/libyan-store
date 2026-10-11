<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Auth;
use App\Models\OrderRepository;
use App\Models\ProductRepository;
use App\Models\UserRepository;

/**
 * نظرة عامة على لوحة الإدارة.
 */
final class DashboardController extends Controller
{
    public function index(): void
    {
        $allProducts = ProductRepository::allForAdmin();

        $lowStockItems = array_values(array_filter(
            $allProducts,
            static fn (array $p): bool => $p['stock'] <= 5
        ));

        $this->view('admin/dashboard', [
            'stats' => [
                'products' => count($allProducts),
                'users' => UserRepository::countAll(),
                'orders' => OrderRepository::countAll(),
                'pending' => OrderRepository::countByStatus()['pending'] ?? 0,
                'lowStock' => count($lowStockItems),
                'revenue' => (float) \App\Core\Database::scalar(
                    "SELECT COALESCE(SUM(total), 0) FROM orders WHERE status != 'cancelled'"
                ),
            ],
            'lowStockItems' => array_slice($lowStockItems, 0, 6),
            'recentOrders' => array_slice(OrderRepository::all(), 0, 5),
            'role' => Auth::role(),
            'page' => [
                'title' => 'لوحة الإدارة - ' . env('APP_NAME', 'المتجر الليبي'),
                'canonical' => '/admin',
                'noindex' => true,
            ],
        ]);
    }
}