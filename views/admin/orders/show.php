<?php

use App\Core\Csrf;
use App\Models\OrderRepository;

/** تفاصيل طلب في اللوحة. المتغيرات: $order, $items, $buyer */
$radius = 0.0;
foreach ($items as $item) {
    $radius += (float) $item['price'] * (int) $item['qty'];
}
?>
<section class="container section">
    <nav class="breadcrumb" aria-label="مسار التنقل">
        <a href="<?= e(url('/admin')) ?>"><?= e(__('admin.dashboard')) ?></a>
        <span aria-hidden="true">/</span>
        <a href="<?= e(url('/admin/orders')) ?>"><?= e(__('admin.orders')) ?></a>
        <span aria-hidden="true">/</span>
        <span>#<?= (int) $order['id'] ?></span>
    </nav>

    <div class="page-head row-between">
        <h1><?= e(__('admin.order_details')) ?> #<?= (int) $order['id'] ?></h1>
        <form method="post" action="<?= e(url('admin/orders/' . $order['id'] . '/status')) ?>" class="inline-form">
            <?= Csrf::field() ?>
            <select name="status">
                <?php foreach (OrderRepository::STATUSES as $status): ?>
                    <option value="<?= e($status) ?>" <?= $order['status'] === $status ? 'selected' : '' ?>>
                        <?= e(OrderRepository::STATUS_LABELS[$status]) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-primary" type="submit"><?= e(__('buttons.save')) ?></button>
        </form>
    </div>

    <div class="admin-shell">
        <?= \App\Core\View::partial('admin/_nav', []) ?>

        <div class="admin-content">
            <div class="grid-2">
                <div class="notice">
                    <h2><?= e(__('admin.customer')) ?></h2>
                    <p><strong><?= e($order['customer_name']) ?></strong></p>
                    <p dir="auto"><?= e($order['customer_phone']) ?></p>
                    <p><?= e($order['customer_city'] . ' - ' . $order['customer_address']) ?></p>
                    <?php if ($order['notes'] !== null && $order['notes'] !== ''): ?>
                        <p class="muted"><?= e(__('checkout.notes')) ?>: <?= e($order['notes']) ?></p>
                    <?php endif; ?>
                    <?php if ($buyer !== null): ?>
                        <p class="muted small"><?= e(__('admin.linked_account')) ?>:
                            <span dir="ltr"><?= e($buyer['email']) ?></span> (<?= e($buyer['name']) ?>)</p>
                    <?php else: ?>
                        <p class="muted small"><?= e(__('admin.guest_order')) ?></p>
                    <?php endif; ?>
                </div>

                <div class="notice">
                    <h2><?= e(__('admin.order_summary')) ?></h2>
                    <div class="summary-row">
                        <span><?= e(__('account.order_date')) ?></span>
                        <strong><?= e($order['created_at']) ?></strong>
                    </div>
                    <div class="summary-row">
                        <span><?= e(__('account.order_status')) ?></span>
                        <strong><?= e(OrderRepository::STATUS_LABELS[$order['status']] ?? $order['status']) ?></strong>
                    </div>
                    <div class="summary-row">
                        <span><?= e(__('cart.shipping')) ?> (<?= e($order['customer_city']) ?>)</span>
                        <strong><?= e(format_price((float) ($order['delivery_fee'] ?? 0))) ?></strong>
                    </div>
                    <div class="summary-row total">
                        <span><?= e(__('cart.total')) ?></span>
                        <strong><?= e(format_price((float) $order['total'])) ?></strong>
                    </div>
                </div>
            </div>

            <div class="table-wrap" style="margin-top:18px">
                <table class="table">
                    <thead>
                    <tr>
                        <th><?= e(__('admin.col_product')) ?></th>
                        <th><?= e(__('cart.col_price')) ?></th>
                        <th><?= e(__('cart.col_qty')) ?></th>
                        <th><?= e(__('cart.col_total')) ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= e($item['product_name']) ?></td>
                            <td><?= e(format_price((float) $item['price'])) ?></td>
                            <td><?= (int) $item['qty'] ?></td>
                            <td><?= e(format_price((float) $item['price'] * (int) $item['qty'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>