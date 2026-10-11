<?php

use App\Core\Csrf;

/** نموذج إضافة/تعديل منتج. المتغيرات: $product (null عند الإضافة), $categories */
$isEdit = $product !== null;
$old = \App\Core\Session::flashData()['old'] ?? [];
$val = static fn (string $key, string $default = ''): string => (string) ($old[$key] ?? $product[$key] ?? $default);
$action = $isEdit
    ? url('admin/products/' . $product['id'] . '/update')
    : url('/admin/products/store');
?>
<section class="container section">
    <nav class="breadcrumb" aria-label="مسار التنقل">
        <a href="<?= e(url('/admin')) ?>"><?= e(__('admin.dashboard')) ?></a>
        <span aria-hidden="true">/</span>
        <a href="<?= e(url('/admin/products')) ?>"><?= e(__('admin.products')) ?></a>
        <span aria-hidden="true">/</span>
        <span><?= $isEdit ? e(__('buttons.edit')) : e(__('admin.add_product')) ?></span>
    </nav>

    <div class="page-head">
        <h1><?= $isEdit ? e($product['name']) : e(__('admin.add_product')) ?></h1>
    </div>

    <div class="admin-shell">
        <?= \App\Core\View::partial('admin/_nav', []) ?>

        <div class="admin-content">
            <form class="form-card" method="post" action="<?= e($action) ?>" enctype="multipart/form-data">
                <?= Csrf::field() ?>

                <div class="grid-2">
                    <label class="field">
                        <span><?= e(__('admin.f_name')) ?> *</span>
                        <input type="text" name="name" required maxlength="150" value="<?= e($val('name')) ?>">
                    </label>

                    <label class="field">
                        <span><?= e(__('admin.f_slug')) ?></span>
                        <input type="text" name="slug" maxlength="150" dir="ltr"
                               value="<?= e($val('slug')) ?>" placeholder="auto">
                        <small class="muted"><?= e(__('admin.f_slug_hint')) ?></small>
                    </label>
                </div>

                <div class="grid-2">
                    <label class="field">
                        <span><?= e(__('admin.f_category')) ?> *</span>
                        <select name="category_id" required>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= (int) $category['id'] ?>"
                                    <?= (string) $val('category_id') === (string) $category['id'] ? 'selected' : '' ?>>
                                    <?= e($category['emoji']) ?> <?= e($category['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <label class="field">
                        <span><?= e(__('admin.f_stock')) ?> *</span>
                        <input type="number" name="stock" required min="0" step="1" value="<?= e($val('stock', '0')) ?>">
                    </label>
                </div>

                <div class="grid-2">
                    <label class="field">
                        <span><?= e(__('admin.f_price')) ?> (<?= e(env('APP_CURRENCY_SYMBOL', 'د.ل')) ?>) *</span>
                        <input type="number" name="price" required min="0" step="0.001" value="<?= e($val('price')) ?>">
                    </label>

                    <label class="field">
                        <span><?= e(__('admin.f_old_price')) ?></span>
                        <input type="number" name="old_price" min="0" step="0.001" value="<?= e($val('old_price')) ?>">
                        <small class="muted"><?= e(__('admin.f_old_price_hint')) ?></small>
                    </label>
                </div>

                <div class="grid-2">
                    <label class="field">
                        <span><?= e(__('admin.f_prep')) ?></span>
                        <input type="number" name="prep_minutes" min="0" max="600" step="1" value="<?= e($val('prep_minutes', '0')) ?>">
                        <small class="muted"><?= e(__('admin.f_prep_hint')) ?></small>
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
                    <span><?= e(__('admin.f_tagline')) ?> *</span>
                    <input type="text" name="tagline" required maxlength="190" value="<?= e($val('tagline')) ?>">
                </label>

                <label class="field">
                    <span><?= e(__('admin.f_description')) ?> *</span>
                    <textarea name="description" required rows="5" maxlength="4000"><?= e($val('description')) ?></textarea>
                </label>

                <label class="field">
                    <span><?= e(__('admin.f_features')) ?></span>
                    <textarea name="features" rows="4"><?= e(implode("\n", is_array($product['features'] ?? null) ? $product['features'] : [])) ?></textarea>
                    <small class="muted"><?= e(__('admin.f_features_hint')) ?></small>
                </label>

                <label class="field">
                    <span><?= e(__('admin.f_image')) ?></span>
                    <?php if ($isEdit && !empty($product['image'])): ?>
                        <div class="image-preview">
                            <img src="<?= e(url('uploads/' . $product['image'])) ?>" alt="" loading="lazy">
                        </div>
                    <?php endif; ?>
                    <input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif">
                    <small class="muted"><?= e(__('admin.f_image_hint', ['max' => env('UPLOAD_MAX_MB', '2')])) ?></small>
                </label>

                <div class="checks">
                    <label class="check">
                        <input type="checkbox" name="is_active" value="1"
                            <?= (string) $val('is_active', $isEdit ? '' : '1') === '1' ? 'checked' : '' ?>>
                        <?= e(__('admin.f_active')) ?>
                    </label>
                    <label class="check">
                        <input type="checkbox" name="featured" value="1"
                            <?= (string) $val('featured') === '1' ? 'checked' : '' ?>>
                        <?= e(__('admin.f_featured')) ?>
                    </label>
                </div>

                <div class="form-actions">
                    <button class="btn btn-primary" type="submit"><?= e(__('buttons.save')) ?></button>
                    <a class="btn btn-outline" href="<?= e(url('/admin/products')) ?>"><?= e(__('buttons.cancel')) ?></a>
                </div>
            </form>
        </div>
    </div>
</section>