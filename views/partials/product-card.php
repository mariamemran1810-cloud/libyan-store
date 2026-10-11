<?php

/** بطاقة منتج قابلة لإعادة الاستخدام — المتغير المطلوب: $product */
?>
<article class="product-card">
    <a class="product-media" href="<?= e($product['url']) ?>" style="background:<?= e($product['color']) ?>"
       aria-label="<?= e($product['name']) ?>">
        <span class="product-emoji" aria-hidden="true"><?= e($product['emoji']) ?></span>
        <?php if (!empty($product['old_price'])): ?>
            <span class="badge badge-sale"><?= (int) round((1 - $product['price'] / $product['old_price']) * 100) ?>%</span>
        <?php endif; ?>
        <?php if ((int) $product['stock'] === 0): ?>
            <span class="badge badge-out"><?= e(__('products.out_of_stock')) ?></span>
        <?php endif; ?>
    </a>

    <div class="product-body">
        <span class="product-category"><?= e($product['category_name']) ?></span>
        <h3 class="product-title">
            <a href="<?= e($product['url']) ?>"><?= e($product['name']) ?></a>
        </h3>
        <p class="product-tagline"><?= e($product['tagline']) ?></p>

        <div class="product-footer">
            <div class="product-price">
                <span class="price"><?= e(format_price($product['price'])) ?></span>
                <?php if (!empty($product['old_price'])): ?>
                    <del class="price-old"><?= e(format_price($product['old_price'])) ?></del>
                <?php endif; ?>
            </div>
            <a class="btn btn-ghost" href="<?= e($product['url']) ?>"><?= e(__('buttons.view_details')) ?></a>
        </div>
    </div>
</article>