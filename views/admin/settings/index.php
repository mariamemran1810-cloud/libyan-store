<?php

use App\Core\Csrf;

/** إعدادات المتجر — للمسؤولة فقط. المتغيرات: $cities, $deliveryNote, $storePhone */
?>
<section class="container section">
    <nav class="breadcrumb" aria-label="مسار التنقل">
        <a href="<?= e(url('/admin')) ?>"><?= e(__('admin.dashboard')) ?></a>
        <span aria-hidden="true">/</span>
        <span><?= e(__('admin.settings')) ?></span>
    </nav>

    <div class="page-head">
        <h1><?= e(__('admin.settings')) ?></h1>
        <p class="muted"><?= e(__('admin.settings_intro')) ?></p>
    </div>

    <div class="admin-shell">
        <?= \App\Core\View::partial('admin/_nav', []) ?>

        <div class="admin-content">
            <form class="form-card" method="post" action="<?= e(url('/admin/settings/update')) ?>">
                <?= Csrf::field() ?>

                <h2><?= e(__('admin.f_delivery_cities')) ?></h2>
                <p class="muted small"><?= e(__('admin.delivery_hint')) ?></p>

                <div class="table-wrap">
                    <table class="table" id="cities-table">
                        <thead>
                        <tr>
                            <th><?= e(__('admin.f_city_name')) ?></th>
                            <th><?= e(__('admin.f_delivery_fee')) ?></th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($cities as $index => $city): ?>
                            <tr>
                                <td>
                                    <input type="text" name="city_name[]" maxlength="60" required
                                           value="<?= e($city['name']) ?>" aria-label="<?= e(__('admin.f_city_name')) ?>">
                                </td>
                                <td>
                                    <input class="qty-input" type="number" name="city_fee[]" step="0.001" min="0"
                                           required value="<?= e(number_format($city['fee'], 3, '.', '')) ?>"
                                           aria-label="<?= e(__('admin.f_delivery_fee')) ?>">
                                </td>
                                <td class="actions">
                                    <button type="button" class="btn btn-danger" data-remove-row><?= e(__('buttons.delete')) ?></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="cart-actions">
                    <button type="button" class="btn btn-outline" id="add-city">+ <?= e(__('admin.add_city')) ?></button>
                </div>

                <label class="field" style="margin-top:18px">
                    <span><?= e(__('admin.f_delivery_note')) ?> *</span>
                    <textarea name="delivery_note" required rows="2" maxlength="300"><?= e($deliveryNote) ?></textarea>
                </label>

                <label class="field">
                    <span><?= e(__('admin.f_store_phone')) ?> *</span>
                    <input type="tel" name="store_phone" required maxlength="30" dir="ltr" style="text-align:right"
                           value="<?= e($storePhone) ?>" placeholder="09XXXXXXXX">
                </label>

                <div class="form-actions">
                    <button class="btn btn-primary" type="submit"><?= e(__('buttons.save')) ?></button>
                    <a class="btn btn-outline" href="<?= e(url('/admin')) ?>"><?= e(__('buttons.cancel')) ?></a>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
    (function () {
        var table = document.getElementById('cities-table');
        var body = table ? table.querySelector('tbody') : null;
        var addBtn = document.getElementById('add-city');
        if (!body || !addBtn) return;

        addBtn.addEventListener('click', function () {
            var row = document.createElement('tr');
            row.innerHTML =
                '<td><input type="text" name="city_name[]" maxlength="60" required></td>' +
                '<td><input class="qty-input" type="number" name="city_fee[]" step="0.001" min="0" required value="0.000"></td>' +
                '<td class="actions"><button type="button" class="btn btn-danger" data-remove-row>حذف</button></td>';
            body.appendChild(row);
            row.querySelector('input').focus();
        });

        table.addEventListener('click', function (event) {
            var target = event.target;
            if (target && target.hasAttribute && target.hasAttribute('data-remove-row')) {
                var row = target.closest('tr');
                if (row && body.querySelectorAll('tr').length > 1) {
                    row.remove();
                }
            }
        });
    })();
</script>
