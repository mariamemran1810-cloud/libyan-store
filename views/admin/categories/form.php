<?php

use App\Core\Csrf;

/** نموذج إضافة/تعديل قسم. المتغير: $category */
$isEdit = $category !== null;
$old = \App\Core\Session::flashData()['old'] ?? [];
$val = static fn (string $key, string $default = ''): string => (string) ($old[$key] ?? $category[$key] ?? $default);
$action = $isEdit ? url('admin/categories/' . $category['id'] . '/update') : url('/admin/categories/store');
?>
<section class="container section">
    <nav class="breadcrumb" aria-label="مسار التنقل">
        <a href="<?= e(url('/admin')) ?>"><?= e(__('admin.dashboard')) ?></a>
        <span aria-hidden="true">/</span>
        <a href="<?= e(url('/admin/categories')) ?>"><?= e(__('admin.categories')) ?></a>
        <span aria-hidden="true">/</span>
        <span><?= $isEdit ? e(__('buttons.edit')) : e(__('admin.add_category')) ?></span>
    </nav>

    <div class="page-head">
        <h1><?= $isEdit ? e($category['name']) : e(__('admin.add_category')) ?></h1>
    </div>

    <div class="admin-shell">
        <?= \App\Core\View::partial('admin/_nav', []) ?>

        <div class="admin-content">
            <form class="form-card" method="post" action="<?= e($action) ?>">
                <?= Csrf::field() ?>

                <div class="grid-2">
                    <label class="field">
                        <span><?= e(__('admin.f_name')) ?> *</span>
                        <input type="text" name="name" required minlength="2" maxlength="80" value="<?= e($val('name')) ?>">
                    </label>

                    <label class="field">
                        <span><?= e(__('admin.f_slug')) ?></span>
                        <input type="text" name="slug" maxlength="80" dir="ltr" value="<?= e($val('slug')) ?>" placeholder="auto">
                        <small class="muted"><?= e(__('admin.f_slug_hint')) ?></small>
                    </label>
                </div>

                <div class="grid-2">
                    <label class="field">
                        <span><?= e(__('admin.f_emoji')) ?></span>
                        <input type="text" name="emoji" maxlength="8" value="<?= e($val('emoji', '🛍️')) ?>">
                    </label>

                    <label class="field">
                        <span><?= e(__('admin.f_color')) ?></span>
                        <input type="color" name="color" value="<?= e($val('color', '#e5e7eb')) ?>">
                    </label>
                </div>

                <label class="field">
                    <span><?= e(__('admin.f_description')) ?> *</span>
                    <textarea name="description" required rows="3" maxlength="250"><?= e($val('description')) ?></textarea>
                </label>

                <div class="form-actions">
                    <button class="btn btn-primary" type="submit"><?= e(__('buttons.save')) ?></button>
                    <a class="btn btn-outline" href="<?= e(url('/admin/categories')) ?>"><?= e(__('buttons.cancel')) ?></a>
                </div>
            </form>
        </div>
    </div>
</section>