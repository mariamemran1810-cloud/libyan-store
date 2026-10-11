<?php

/** صفحة المنتج. المتغيرات: $product, $related */
$categoryUrl = url('category/' . $product['category']);

// بيانات منظمة (JSON-LD) تساعد Google/Bing على فهم المنتج وسعره
$ld = [
    '@context' => 'https://schema.org',
    '@type' => 'Product',
    'name' => $product['name'],
    'description' => $product['tagline'],
    'url' => absolute_url($product['url']),
    'category' => $product['category_name'],
    'offers' => [
        '@type' => 'Offer',
        'price' => number_format((float) $product['price'], 3, '.', ''),
        'priceCurrency' => env('APP_CURRENCY', 'LYD'),
        'availability' => (int) $product['stock'] > 0
            ? 'https://schema.org/InStock'
            : 'https://schema.org/OutOfStock',
        'url' => absolute_url($product['url']),
    ],
];
?>
<script type="application/ld+json"><?= json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>

<section class="container section">
    <nav class="breadcrumb" aria-label="مسار التنقل">
        <a href="<?= e(url('/')) ?>"><?= e(__('breadcrumbs.home')) ?></a>
        <span aria-hidden="true">/</span>
        <a href="<?= e(url('/products')) ?>"><?= e(__('nav.products')) ?></a>
        <span aria-hidden="true">/</span>
        <a href="<?= e($categoryUrl) ?>"><?= e($product['category_name']) ?></a>
        <span aria-hidden="true">/</span>
        <span><?= e($product['name']) ?></span>
    </nav>

    <div class="product-detail">
        <div class="product-detail-media" style="background:<?= e($product['color']) ?>">
            <span class="product-emoji big" aria-hidden="true"><?= e($product['emoji']) ?></span>
            <?php if (!empty($product['old_price'])): ?>
                <span class="badge badge-sale">
                    خصم <?= (int) round((1 - $product['price'] / $product['old_price']) * 100) ?>%
                </span>
            <?php endif; ?>
        </div>

        <div class="product-detail-info">
            <span class="product-category"><?= e($product['category_name']) ?></span>
            <h1><?= e($product['name']) ?></h1>
            <p class="product-tagline"><?= e($product['tagline']) ?></p>

            <div class="product-price big">
                <span class="price"><?= e(format_price($product['price'])) ?></span>
                <?php if (!empty($product['old_price'])): ?>
                    <del class="price-old"><?= e(format_price($product['old_price'])) ?></del>
                <?php endif; ?>
            </div>

            <p class="stock <?= (int) $product['stock'] > 0 ? 'in' : 'out' ?>">
                <?= (int) $product['stock'] > 0
                    ? e(__('products.in_stock', ['count' => (string) $product['stock']]))
                    : e(__('products.out_of_stock')) ?>
            </p>

            <?php if ((int) ($product['prep_minutes'] ?? 0) > 0): ?>
                <p class="prep-time"><?= e(__('product.prep_time', ['min' => (string) $product['prep_minutes']])) ?></p>
            <?php endif; ?>

            <h2><?= e(__('products.description')) ?></h2>
            <p><?= e($product['description']) ?></p>

            <h3><?= e(__('products.features')) ?></h3>
            <ul class="feature-list">
                <?php foreach ($product['features'] as $feature): ?>
                    <li><?= e($feature) ?></li>
                <?php endforeach; ?>
            </ul>

            <?php if ((int) $product['stock'] > 0): ?>
                <form class="detail-actions add-to-cart" method="post" action="<?= e(url('/cart/add')) ?>">
                    <?= \App\Core\Csrf::field() ?>
                    <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                    <input type="hidden" name="return_to" value="<?= e($product['url']) ?>">
                    <label class="qty-label">
                        <span><?= e(__('cart.col_qty')) ?></span>
                        <input class="qty-input" type="number" name="qty" value="1" min="1" max="<?= (int) $product['stock'] ?>">
                    </label>
                    <button class="btn btn-primary" type="submit"><?= e(__('buttons.add_to_cart')) ?></button>
                    <a class="btn btn-outline" href="<?= e($categoryUrl) ?>"><?= e(__('products.more_from_category')) ?></a>
                </form>
            <?php else: ?>
                <div class="detail-actions">
                    <span class="btn btn-outline out"><?= e(__('products.out_of_stock')) ?></span>
                    <a class="btn btn-outline" href="<?= e($categoryUrl) ?>"><?= e(__('products.more_from_category')) ?></a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($related !== []): ?>
        <div class="section-head">
            <h2><?= e(__('products.related')) ?></h2>
        </div>
        <div class="product-grid">
            <?php foreach ($related as $item): ?>
                <?= \App\Core\View::partial('partials/product-card', ['product' => $item]) ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>