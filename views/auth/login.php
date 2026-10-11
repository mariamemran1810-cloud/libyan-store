<?php

use App\Core\Csrf;
?>
<section class="container section auth-page">
    <div class="auth-card">
        <h1><?= e(__('auth.login_title')) ?></h1>
        <p class="muted"><?= e(__('auth.login_intro')) ?></p>

        <form method="post" action="<?= e(url('/login')) ?>" novalidate>
            <?= Csrf::field() ?>

            <label class="field">
                <span><?= e(__('auth.email')) ?></span>
                <input type="email" name="email" required maxlength="190" autocomplete="email"
                       placeholder="name@example.com">
            </label>

            <label class="field">
                <span><?= e(__('auth.password')) ?></span>
                <input type="password" name="password" required maxlength="100" autocomplete="current-password">
            </label>

            <button class="btn btn-primary btn-block" type="submit"><?= e(__('nav.login')) ?></button>
        </form>

        <p class="muted small center"><?= e(__('auth.no_account')) ?>
            <a href="<?= e(url('/register')) ?>"><?= e(__('nav.register')) ?></a></p>
    </div>
</section>