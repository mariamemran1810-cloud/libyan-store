<?php

use App\Core\Csrf;
use App\Models\OrderRepository;

/** إدارة الطلبات. المتغير: $orders */
?>
<section class="container section">
    <nav class="breadcrumb" aria-label="مسار التنقل">
        <a href="<?= e(url('/admin')) ?>"><?= e(__('admin.dashboard')) ?></a>
        <span aria-hidden="true">/</span>
        <span><?= e(__('admin.orders')) ?></span>
    </nav>

    <div class="page-head">
        <h1><?= e(__('admin.orders')) ?></h1>
        <p class="muted"><?= e(__('admin.orders_count', ['count' => (string) count($orders)])) ?></p>
    </div>

    <div class="admin-shell">
        <?= \App\Core\View::partial('admin/_nav', []) ?>

        <div class="admin-content">
            <?php if ($orders === []): ?>
                <div class="notice"><p class="muted"><?= e(__('admin.no_orders_yet')) ?></p></div>
            <?php else: ?>
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                        <tr>
                            <th><?= e(__('account.order_no')) ?></th>
                            <th><?= e(__('admin.customer')) ?></th>
                            <th><?= e(__('admin.contact')) ?></th>
                            <th><?= e(__('admin.address')) ?></th>
                            <th><?= e(__('account.order_total')) ?></th>
                            <th><?= e(__('admin.col_status')) ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>
                                    <a class="strong-link" href="<?= e(url('admin/orders/' . $order['id'])) ?>">
                                        <strong>#<?= (int) $order['id'] ?></strong>
                                    </a><br>
                                    <span class="muted small"><?= e($order['created_at']) ?> —
                                        <?= (int) $order['items_count'] ?> <?= e(__('admin.items')) ?></span>
                                </td>
                                <td><?= e($order['customer_name']) ?></td>
                                <td dir="auto"><?= e($order['customer_phone']) ?></td>
                                <td><?= e($order['customer_city'] . ' - ' . $order['customer_address']) ?></td>
                                <td><?= e(format_price((float) $order['total'])) ?></td>
                                <td>
                                    <form method="post" action="<?= e(url('admin/orders/' . $order['id'] . '/status')) ?>"
                                          class="inline-form">
                                        <?= Csrf::field() ?>
                                        <select name="status" onchange="this.form.submit()">
                                            <?php foreach (OrderRepository::STATUSES as $status): ?>
                                                <option value="<?= e($status) ?>" <?= $order['status'] === $status ? 'selected' : '' ?>>
                                                    <?= e(OrderRepository::STATUS_LABELS[$status]) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>