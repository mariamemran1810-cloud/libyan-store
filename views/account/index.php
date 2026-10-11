<?php

/** صفحة الحساب. المتغيرات: $user, $orders */
$roleLabels = [
    'admin' => 'مسؤولة',
    'seller' => 'بائعة',
    'customer' => 'عميلة',
];
$statusLabels = [
    'pending' => 'قيد المراجعة',
    'confirmed' => 'مؤكّد',
    'shipped' => 'قيد الشحن',
    'delivered' => 'تم التسليم',
    'cancelled' => 'ملغي',
];
?>
<section class="container section">
    <nav class="breadcrumb" aria-label="مسار التنقل">
        <a href="<?= e(url('/')) ?>"><?= e(__('breadcrumbs.home')) ?></a>
        <span aria-hidden="true">/</span>
        <span><?= e(__('nav.account')) ?></span>
    </nav>

    <div class="page-head row-between">
        <div>
            <h1><?= e(__('account.title', ['name' => $user['name'] ?? ''])) ?></h1>
            <p class="muted"><?= e($user['email'] ?? '') ?> — <?= e($roleLabels[$user['role'] ?? 'customer'] ?? '') ?></p>
        </div>
        <a class="btn btn-outline" href="<?= e(url('/account/password')) ?>"><?= e(__('password.title')) ?></a>
    </div>

    <div class="notice">
        <h2><?= e(__('account.my_orders')) ?></h2>
        <?php if ($orders === []): ?>
            <p class="muted"><?= e(__('account.no_orders')) ?></p>
            <a class="btn btn-primary" href="<?= e(url('/products')) ?>"><?= e(__('buttons.browse_products')) ?></a>
        <?php else: ?>
            <table class="table">
                <thead>
                <tr>
                    <th><?= e(__('account.order_no')) ?></th>
                    <th><?= e(__('account.order_date')) ?></th>
                    <th><?= e(__('account.order_total')) ?></th>
                    <th><?= e(__('account.order_status')) ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>
                            <a class="strong-link" href="<?= e(url('/order/' . (int) $order['id'])) ?>">#<?= (int) $order['id'] ?></a>
                        </td>
                        <td><?= e($order['created_at']) ?></td>
                        <td><?= e(format_price((float) $order['total'])) ?></td>
                        <td><?= e($statusLabels[$order['status']] ?? $order['status']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</section>