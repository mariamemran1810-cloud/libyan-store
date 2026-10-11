<?php

use App\Core\Csrf;

/** إدارة المنتجات. المتغير: $products */
?>
<section class="container section">
    <nav class="breadcrumb" aria-label="مسار التنقل">
        <a href="<?= e(url('/admin')) ?>"><?= e(__('admin.dashboard')) ?></a>
        <span aria-hidden="true">/</span>
        <span><?= e(__('admin.products')) ?></span>
    </nav>

    <div class="page-head row-between">
        <h1><?= e(__('admin.products')) ?></h1>
        <a class="btn btn-primary" href="<?= e(url('/admin/products/create')) ?>"><?= e(__('admin.add_product')) ?></a>
    </div>

    <div class="admin-shell">
        <?= \App\Core\View::partial('admin/_nav', []) ?>

        <div class="admin-content">
            <div class="table-wrap">
                <table class="table">
                    <thead>
                    <tr>
                        <th><?= e(__('admin.col_product')) ?></th>
                        <th><?= e(__('admin.col_category')) ?></th>
                        <th><?= e(__('admin.col_price')) ?></th>
                        <th><?= e(__('admin.col_stock')) ?></th>
                        <th><?= e(__('admin.col_status')) ?></th>
                        <th><?= e(__('admin.col_actions')) ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td>
                                <strong><?= e($product['name']) ?></strong><br>
                                <span class="muted small">/products/<?= e($product['slug']) ?></span>
                            </td>
                            <td><?= e($product['category_name']) ?></td>
                            <td><?= e(format_price($product['price'])) ?></td>
                            <td>
                                <span class="<?= $product['stock'] <= 5 ? 'stock out' : '' ?>">
                                    <?= (int) $product['stock'] ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($product['is_active']): ?>
                                    <span class="pill ok"><?= e(__('admin.published')) ?></span>
                                <?php else: ?>
                                    <span class="pill"><?= e(__('admin.draft')) ?></span>
                                <?php endif; ?>
                                <?php if ($product['featured']): ?>
                                    <span class="pill star">★</span>
                                <?php endif; ?>
                            </td>
                            <td class="actions">
                                <a class="btn btn-ghost" href="<?= e(url('admin/products/' . $product['id'] . '/edit')) ?>">
                                    <?= e(__('buttons.edit')) ?>
                                </a>
                                <form method="post" action="<?= e(url('admin/products/' . $product['id'] . '/delete')) ?>"
                                      onsubmit="return confirm('هل أنتِ متأكدة من حذف هذا المنتج؟')">
                                    <?= Csrf::field() ?>
                                    <button type="submit" class="btn btn-danger"><?= e(__('buttons.delete')) ?></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>