<?php

use App\Core\Csrf;

/** السلة. المتغيرات: $items, $subtotal */
?>
<section class="container section">
    <nav class="breadcrumb" aria-label="مسار التنقل">
        <a href="<?= e(url('/')) ?>"><?= e(__('breadcrumbs.home')) ?></a>
        <span aria-hidden="true">/</span>
        <span><?= e(__('nav.cart')) ?></span>
    </nav>

    <div class="page-head">
        <h1><?= e(__('cart.title')) ?></h1>
        <p class="muted"><?= e(__('cart.items_count', ['count' => (string) count($items)])) ?></p>
    </div>

    <?php if ($items === []): ?>
        <div class="notice empty-state">
            <p><?= e(__('cart.empty')) ?></p>
            <a class="btn btn-primary" href="<?= e(url('/products')) ?>"><?= e(__('buttons.browse_products')) ?></a>
        </div>
        <?php return; ?>
    <?php endif; ?>

    <div class="cart-layout">
        <form method="post" action="<?= e(url('/cart/update')) ?>" class="cart-list">
            <?= Csrf::field() ?>

            <div class="table-wrap">
                <table class="table">
                    <thead>
                    <tr>
                        <th><?= e(__('cart.col_product')) ?></th>
                        <th><?= e(__('cart.col_price')) ?></th>
                        <th><?= e(__('cart.col_qty')) ?></th>
                        <th><?= e(__('cart.col_total')) ?></th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <div class="cart-product">
                                    <span class="cart-thumb" style="background:<?= e($item['color']) ?>"><?= e($item['emoji']) ?></span>
                                    <div>
                                        <a href="<?= e($item['url']) ?>"><?= e($item['name']) ?></a><br>
                                        <span class="muted small"><?= e(__('cart.in_stock', ['count' => (string) $item['stock']])) ?></span>
                                    </div>
                                </div>
                            </td>
                            <td><?= e(format_price($item['price'])) ?></td>
                            <td>
                                <input class="qty-input" type="number" name="qty[<?= (int) $item['id'] ?>]"
                                       value="<?= (int) $item['qty'] ?>" min="0" max="<?= (int) $item['stock'] ?>"
                                       aria-label="<?= e(__('cart.col_qty')) ?>">
                            </td>
                            <td><strong><?= e(format_price($item['line_total'])) ?></strong></td>
                            <td>
                                <button type="submit" form="remove-<?= (int) $item['id'] ?>" class="btn btn-danger">
                                    <?= e(__('buttons.delete')) ?>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="cart-actions">
                <button class="btn btn-primary" type="submit"><?= e(__('buttons.save')) ?></button>
                <a class="btn btn-outline" href="<?= e(url('/products')) ?>"><?= e(__('buttons.continue_shopping')) ?></a>
            </div>
        </form>

        <?php foreach ($items as $item): ?>
            <form id="remove-<?= (int) $item['id'] ?>" method="post" action="<?= e(url('/cart/remove')) ?>">
                <?= Csrf::field() ?>
                <input type="hidden" name="product_id" value="<?= (int) $item['id'] ?>">
            </form>
        <?php endforeach; ?>

        <aside class="cart-summary">
            <h2><?= e(__('cart.summary')) ?></h2>
            <div class="summary-row">
                <span><?= e(__('cart.subtotal')) ?></span>
                <strong><?= e(format_price($subtotal)) ?></strong>
            </div>
            <div class="summary-row muted">
                <span><?= e(__('cart.shipping')) ?></span>
                <span><?= e(__('cart.shipping_note')) ?></span>
            </div>
            <div class="summary-row total">
                <span><?= e(__('cart.total')) ?></span>
                <strong><?= e(format_price($subtotal)) ?></strong>
            </div>
            <a class="btn btn-primary btn-block" href="<?= e(url('/checkout')) ?>"><?= e(__('buttons.checkout')) ?></a>
            <p class="muted small"><?= e(__('cart.secure_note')) ?></p>
        </aside>
    </div>
</section>