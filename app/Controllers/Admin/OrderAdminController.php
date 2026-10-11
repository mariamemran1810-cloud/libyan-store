<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Csrf;
use App\Core\Errors;
use App\Models\OrderRepository;

/**
 * إدارة الطلبات.
 */
final class OrderAdminController extends Controller
{
    public function index(): void
    {
        $this->view('admin/orders/index', [
            'orders' => OrderRepository::all(),
            'page' => [
                'title' => 'إدارة الطلبات - ' . env('APP_NAME', 'المتجر الليبي'),
                'canonical' => '/admin/orders',
                'noindex' => true,
            ],
        ]);
    }

    /** تفاصيل طلب واحد: /admin/orders/{id} */
    public function show(string $id): void
    {
        $order = OrderRepository::find((int) $id);
        if ($order === null) {
            Errors::notFoundPage();
            return;
        }

        $this->view('admin/orders/show', [
            'order' => $order,
            'items' => OrderRepository::items((int) $id),
            'buyer' => $order['user_id'] === null
                ? null
                : \App\Models\UserRepository::find((int) $order['user_id']),
            'page' => [
                'title' => 'الطلب #' . (int) $id . ' - ' . env('APP_NAME', 'المتجر الليبي'),
                'canonical' => '/admin/orders/' . $id,
                'noindex' => true,
            ],
        ]);
    }

    public function status(string $id): void
    {
        Csrf::verify();

        $order = OrderRepository::find((int) $id);
        if ($order === null) {
            Errors::notFoundPage();
            return;
        }

        $status = (string) ($_POST['status'] ?? '');
        if (!in_array($status, OrderRepository::STATUSES, true)) {
            flash('errors', 'حالة غير مسموحة.', 'error');
            redirect('/admin/orders');
        }

        OrderRepository::updateStatus((int) $id, $status);

        flash('admin', 'تم تحديث حالة الطلب #' . (int) $id . ' إلى: ' . OrderRepository::STATUS_LABELS[$status], 'success');
        redirect('/admin/orders');
    }
}