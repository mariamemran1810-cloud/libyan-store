<?php

use App\Core\Csrf;

/** إدارة الأقسام — للمسؤولة فقط. المتغير: $categories */
?>
<section class="container section">
    <nav class="breadcrumb" aria-label="مسار التنقل">
        <a href="<?= e(url('/admin')) ?>"><?= e(__('admin.dashboard')) ?></a>
        <span aria-hidden="true">/</span>
        <span><?= e(__('admin.categories')) ?></span>
    </nav>

    <div class="page-head row-between">
        <h1><?= e(__('admin.categories')) ?></h1>
        <a class="btn btn-primary" href="<?= e(url('/admin/categories/create')) ?>"><?= e(__('admin.add_category')) ?></a>
    </div>

    <div class="admin-shell">
        <?= \App\Core\View::partial('admin/_nav', []) ?>

        <div class="admin-content">
            <div class="table-wrap">
                <table class="table">
                    <thead>
                    <tr>
                        <th><?= e(__('admin.col_category')) ?></th>
                        <th><?= e(__('admin.col_slug')) ?></th>
                        <th><?= e(__('admin.col_products_count')) ?></th>
                        <th><?= e(__('admin.col_actions')) ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td>
                                <span class="category-emoji small" style="background:<?= e($category['color']) ?>"><?= e($category['emoji']) ?></span>
                                <strong><?= e($category['name']) ?></strong>
                                <br><span class="muted small"><?= e($category['description']) ?></span>
                            </td>
                            <td dir="ltr" class="muted">/category/<?= e($category['slug']) ?></td>
                            <td><?= (int) $category['products_count'] ?></td>
                            <td class="actions">
                                <a class="btn btn-ghost" href="<?= e(url('admin/categories/' . $category['id'] . '/edit')) ?>">
                                    <?= e(__('buttons.edit')) ?>
                                </a>
                                <form method="post" action="<?= e(url('admin/categories/' . $category['id'] . '/delete')) ?>"
                                      onsubmit="return confirm('حذف هذا القسم نهائيًا؟')">
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