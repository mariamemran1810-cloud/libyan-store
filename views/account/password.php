<?php

use App\Core\Csrf;

/** تغيير كلمة المرور */
?>
<section class="container section">
    <nav class="breadcrumb" aria-label="مسار التنقل">
        <a href="<?= e(url('/')) ?>"><?= e(__('breadcrumbs.home')) ?></a>
        <span aria-hidden="true">/</span>
        <a href="<?= e(url('/account')) ?>"><?= e(__('nav.account')) ?></a>
        <span aria-hidden="true">/</span>
        <span><?= e(__('password.title')) ?></span>
    </nav>

    <div class="page-head">
        <h1><?= e(__('password.title')) ?></h1>
        <p class="muted"><?= e(__('password.intro')) ?></p>
    </div>

    <div class="auth-card">
        <form method="post" action="<?= e(url('/account/password')) ?>">
            <?= Csrf::field() ?>

            <label class="field">
                <span><?= e(__('password.current')) ?> *</span>
                <input type="password" name="current_password" required maxlength="100" autocomplete="current-password">
            </label>

            <label class="field">
                <span><?= e(__('password.new')) ?> *</span>
                <input type="password" name="password" required minlength="8" maxlength="200" autocomplete="new-password">
                <small class="muted"><?= e(__('auth.password_hint')) ?></small>
            </label>

            <label class="field">
                <span><?= e(__('password.confirm')) ?> *</span>
                <input type="password" name="password_confirmation" required maxlength="200" autocomplete="new-password">
            </label>

            <div class="form-actions">
                <button class="btn btn-primary" type="submit"><?= e(__('buttons.save')) ?></button>
                <a class="btn btn-outline" href="<?= e(url('/account')) ?>"><?= e(__('buttons.cancel')) ?></a>
            </div>
        </form>
    </div>
</section>