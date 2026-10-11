<?php

use App\Core\Csrf;

/** إتمام الطلب. المتغيرات: $items, $subtotal, $customer */
$old = \App\Core\Session::flashData()['old'] ?? [];
$val = static fn (string $key): string => (string) ($old[$key] ?? $customer[$key] ?? '');
?>
<section class="container section">
    <nav class="breadcrumb" aria-label="مسار التنقل">
        <a href="<?= e(url('/')) ?>"><?= e(__('breadcrumbs.home')) ?></a>
        <span aria-hidden="true">/</span>
        <a href="<?= e(url('/cart')) ?>"><?= e(__('nav.cart')) ?></a>
        <span aria-hidden="true">/</span>
        <span><?= e(__('checkout.title')) ?></span>
    </nav>

    <div class="page-head">
        <h1><?= e(__('checkout.title')) ?></h1>
        <p class="muted"><?= e(__('checkout.intro')) ?></p>
    </div>

    <div class="checkout-layout">
        <form class="form-card" method="post" action="<?= e(url('/checkout')) ?>">
            <?= Csrf::field() ?>

            <h2><?= e(__('checkout.customer_data')) ?></h2>

            <label class="field">
                <span><?= e(__('auth.name')) ?> *</span>
                <input type="text" name="name" required minlength="2" maxlength="100" value="<?= e($val('name')) ?>">
            </label>

            <div class="grid-2">
                <label class="field">
                    <span><?= e(__('auth.phone')) ?> *</span>
                    <input type="tel" name="phone" required minlength="8" maxlength="30"
                           dir="ltr" style="text-align:right" value="<?= e($val('phone')) ?>" placeholder="09XXXXXXXX">
                </label>

                <label class="field">
                    <span><?= e(__('checkout.city')) ?> *</span>
                    <select name="city" required>
                        <option value=""><?= e(__('checkout.choose_city')) ?></option>
                        <?php foreach ($cities as $city): ?>
                            <option value="<?= e($city['name']) ?>" <?= $val('city') === $city['name'] ? 'selected' : '' ?>>
                                <?= e($city['name']) ?> — <?= e(format_price($city['fee'])) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>

            <?php if ($deliveryNote !== ''): ?>
                <p class="muted small delivery-note">🚚 <?= e($deliveryNote) ?></p>
            <?php endif; ?>

            <label class="field">
                <span><?= e(__('checkout.address')) ?> *</span>
                <input type="text" name="address" required minlength="5" maxlength="250"
                       value="<?= e($val('address')) ?>" placeholder="<?= e(__('checkout.address_hint')) ?>">
            </label>

            <label class="field">
                <span><?= e(__('checkout.notes')) ?></span>
                <textarea name="notes" rows="3" maxlength="500"><?= e($val('notes')) ?></textarea>
                <small class="muted"><?= e(__('checkout.notes_hint')) ?></small>
            </label>

            <div class="form-actions">
                <button class="btn btn-primary" type="submit"><?= e(__('buttons.confirm_order')) ?></button>
                <a class="btn btn-outline" href="<?= e(url('/cart')) ?>"><?= e(__('buttons.back_to_cart')) ?></a>
            </div>
        </form>

        <aside class="cart-summary">
            <h2><?= e(__('checkout.your_order')) ?></h2>
            <ul class="order-lines">
                <?php foreach ($items as $item): ?>
                    <li>
                        <span><?= e($item['emoji']) ?> <?= e($item['name']) ?> × <?= (int) $item['qty'] ?></span>
                        <strong><?= e(format_price($item['line_total'])) ?></strong>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="summary-row muted">
                <span><?= e(__('cart.shipping')) ?></span>
                <span><?= e(__('checkout.fee_by_city')) ?></span>
            </div>
            <div class="summary-row total">
                <span><?= e(__('cart.total')) ?></span>
                <strong><?= e(format_price($subtotal)) ?></strong>
            </div>
            <p class="muted small"><?= e(__('checkout.payment_note')) ?></p>
        </aside>
    </div>
</section>