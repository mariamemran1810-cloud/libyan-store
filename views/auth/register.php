<?php

use App\Core\Csrf;
?>
<section class="container section auth-page">
    <div class="auth-card">
        <h1><?= e(__('auth.register_title')) ?></h1>
        <p class="muted"><?= e(__('auth.register_intro')) ?></p>

        <form method="post" action="<?= e(url('/register')) ?>" novalidate>
            <?= Csrf::field() ?>

            <label class="field">
                <span><?= e(__('auth.name')) ?></span>
                <input type="text" name="name" required minlength="2" maxlength="100" autocomplete="name">
            </label>

            <label class="field">
                <span><?= e(__('auth.email')) ?></span>
                <input type="email" name="email" required maxlength="190" autocomplete="email"
                       placeholder="name@example.com">
            </label>

            <label class="field">
                <span><?= e(__('auth.phone')) ?></span>
                <input type="tel" name="phone" required maxlength="30" autocomplete="tel"
                       placeholder="09XXXXXXXX" dir="ltr" style="text-align:right">
            </label>

            <label class="field">
                <span><?= e(__('auth.password')) ?></span>
                <input type="password" name="password" required minlength="8" maxlength="200" autocomplete="new-password">
                <small class="muted"><?= e(__('auth.password_hint')) ?></small>
            </label>

            <label class="field">
                <span><?= e(__('auth.password_confirm')) ?></span>
                <input type="password" name="password_confirmation" required maxlength="200" autocomplete="new-password">
            </label>

            <button class="btn btn-primary btn-block" type="submit"><?= e(__('nav.register')) ?></button>
        </form>

        <p class="muted small center"><?= e(__('auth.have_account')) ?>
            <a href="<?= e(url('/login')) ?>"><?= e(__('nav.login')) ?></a></p>
    </div>
</section>