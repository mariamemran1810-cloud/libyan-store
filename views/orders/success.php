<?php

use App\Models\OrderRepository;

/** تأكيد الطلب. المتغيرات: $order, $items */
?>
<section class="container section">
    <div class="success-box">
        <div class="success-icon" aria-hidden="true">✅</div>
        <h1><?= e(__('order.success_title', ['id' => (string) $order['id']])) ?></h1>
        <p class="lead"><?= e(__('order.success_text')) ?></p>

        <div class="order-card">
            <div class="summary-row">
                <span><?= e(__('account.order_no')) ?></span>
                <strong>#<?= (int) $order['id'] ?></strong>
            </div>
            <div class="summary-row">
                <span><?= e(__('account.order_date')) ?></span>
                <strong><?= e($order['created_at']) ?></strong>
            </div>
            <div class="summary-row">
                <span><?= e(__('account.order_status')) ?></span>
                <strong><?= e(OrderRepository::STATUS_LABELS[$order['status']] ?? $order['status']) ?></strong>
            </div>
            <div class="summary-row">
                <span><?= e(__('admin.address')) ?></span>
                <strong><?= e($order['customer_name'] . ' - ' . $order['customer_phone']) ?></strong>
            </div>
            <div class="summary-row">
                <span><?= e(__('cart.shipping')) ?> (<?= e($order['customer_city']) ?>)</span>
                <strong><?= e(format_price((float) ($order['delivery_fee'] ?? 0))) ?></strong>
            </div>

            <ul class="order-lines">
                <?php foreach ($items as $item): ?>
                    <li>
                        <span><?= e($item['product_name']) ?> × <?= (int) $item['qty'] ?></span>
                        <strong><?= e(format_price((float) $item['price'] * (int) $item['qty'])) ?></strong>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="summary-row total">
                <span><?= e(__('cart.total')) ?></span>
                <strong><?= e(format_price((float) $order['total'])) ?></strong>
            </div>
        </div>

        <p class="muted"><?= e(__('order.contact_note')) ?></p>

        <div class="hero-actions" style="justify-content:center">
            <a class="btn btn-primary" href="<?= e(url('/products')) ?>"><?= e(__('buttons.continue_shopping')) ?></a>
            <?php if (\App\Core\Auth::check()): ?>
                <a class="btn btn-outline" href="<?= e(url('/account')) ?>"><?= e(__('nav.account')) ?></a>
            <?php endif; ?>
        </div>
    </div>
</section>