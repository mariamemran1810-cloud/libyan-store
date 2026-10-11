<?php

/** قائمة المنتجات: بحث + أقسام + فرز + صفحات. المتغيرات: $result,$query,$currentCategory,$sort,$categories,$heading */
?>
<section class="container section">
    <nav class="breadcrumb" aria-label="مسار التنقل">
        <a href="<?= e(url('/')) ?>"><?= e(__('breadcrumbs.home')) ?></a>
        <span aria-hidden="true">/</span>
        <span><?= e(__('nav.products')) ?></span>
    </nav>

    <div class="page-head">
        <h1><?= e($heading) ?></h1>
        <p class="muted"><?= e(__('products.count', ['count' => (string) $result['total']])) ?></p>
    </div>

    <form class="filter-bar" action="<?= e(url('/products')) ?>" method="get" role="search">
        <input type="search" name="q" value="<?= e($query) ?>" placeholder="<?= e(__('products.search_placeholder')) ?>"
               aria-label="<?= e(__('products.search_placeholder')) ?>">
        <?php if ($currentCategory !== ''): ?>
            <input type="hidden" name="category" value="<?= e($currentCategory) ?>">
        <?php endif; ?>
        <button type="submit" class="btn btn-primary"><?= e(__('products.search_button')) ?></button>
    </form>

    <div class="chip-row">
        <a class="chip <?= $currentCategory === '' ? 'chip-active' : '' ?>"
           href="<?= e(url($query !== '' ? '/products?' . http_build_query(['q' => $query]) : '/products')) ?>">
            <?= e(__('products.all_categories')) ?>
        </a>
        <?php foreach ($categories as $category): ?>
            <a class="chip <?= $currentCategory === $category['slug'] ? 'chip-active' : '' ?>"
               href="<?= e(url('category/' . $category['slug'])) ?>">
                <?= e($category['emoji']) ?> <?= e($category['name']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="sort-row">
        <span><?= e(__('products.sort_label')) ?>:</span>
        <?php
        $sorts = [
            'newest' => __('products.sort_newest'),
            'price_asc' => __('products.sort_price_asc'),
            'price_desc' => __('products.sort_price_desc'),
        ];
        foreach ($sorts as $key => $label):
            $params = ['sort' => $key];
            if ($query !== '') {
                $params['q'] = $query;
            }
            if ($currentCategory !== '') {
                $params['category'] = $currentCategory;
            }
            ?>
            <a class="chip <?= $sort === $key ? 'chip-active' : '' ?>"
               href="<?= e('/products?' . http_build_query($params)) ?>"><?= e($label) ?></a>
        <?php endforeach; ?>
    </div>

    <?php if ($result['items'] === []): ?>
        <div class="notice empty-state">
            <p><?= e(__('products.no_results')) ?></p>
            <a class="btn btn-primary" href="<?= e(url('/products')) ?>"><?= e(__('buttons.browse_products')) ?></a>
        </div>
    <?php else: ?>
        <div class="product-grid">
            <?php foreach ($result['items'] as $product): ?>
                <?= \App\Core\View::partial('partials/product-card', ['product' => $product]) ?>
            <?php endforeach; ?>
        </div>

        <?php if ($result['pages'] > 1): ?>
            <nav class="pagination" aria-label="التنقل بين الصفحات">
                <?php for ($i = 1; $i <= $result['pages']; $i++): ?>
                    <?php
                    $params = ['page' => (string) $i];
                    if ($query !== '') {
                        $params['q'] = $query;
                    }
                    if ($currentCategory !== '') {
                        $params['category'] = $currentCategory;
                    }
                    if ($sort !== 'newest') {
                        $params['sort'] = $sort;
                    }
                    ?>
                    <a class="page-link <?= $i === $result['page'] ? 'page-active' : '' ?>"
                       href="<?= e('/products?' . http_build_query($params)) ?>"
                       <?= $i === $result['page'] ? 'aria-current="page"' : '' ?>><?= $i ?></a>
                <?php endfor; ?>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</section>