<?php

declare(strict_types=1);

use App\Core\Csrf;
use App\Core\Lang;

$flash = function_exists('flash_messages') ? flash_messages() : [];
?>
<!doctype html>
<html lang="<?= e(Lang::locale()) ?>" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0f766e">
    <link rel="icon" type="image/svg+xml" href="<?= e(url('/favicon.svg')) ?>">
    <title><?= e($page['title'] ?? 'المتجر الليبي') ?></title>
    <?php if (!empty($page['description'])): ?>
        <meta name="description" content="<?= e($page['description']) ?>">
    <?php endif; ?>
    <link rel="canonical" href="<?= e(rtrim((string) env('APP_URL', ''), '/') . ($page['canonical'] ?? '/')) ?>">
    <?php if (!empty($page['noindex'])): ?>
        <meta name="robots" content="noindex, follow">
    <?php endif; ?>
    <meta property="og:site_name" content="<?= e(env('APP_NAME', 'المتجر الليبي')) ?>">
    <meta property="og:title" content="<?= e($page['title'] ?? 'المتجر الليبي') ?>">
    <meta property="og:description" content="<?= e($page['description'] ?? '') ?>">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="ar_LY">
    <meta property="og:url" content="<?= e(rtrim((string) env('APP_URL', ''), '/') . ($page['canonical'] ?? '/')) ?>">
    <meta name="twitter:card" content="summary">
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
</head>
<body class="<?= e($page['body_class'] ?? '') ?>">
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="<?= e(url('/')) ?>">
            <span class="brand-mark">🇱🇾</span>
            <span class="brand-text"><?= e(env('APP_NAME', 'المتجر الليبي')) ?></span>
        </a>

        <form class="search-form" action="<?= e(url('/products')) ?>" method="get" role="search">
            <input type="search" name="q" placeholder="<?= e(__('nav.search_placeholder')) ?>" aria-label="<?= e(__('nav.search_placeholder')) ?>">
            <button type="submit"><?= e(__('buttons.search')) ?></button>
        </form>

        <nav class="main-nav" aria-label="القائمة الرئيسية">
            <a href="<?= e(url('/')) ?>"><?= e(__('nav.home')) ?></a>
            <a href="<?= e(url('/categories')) ?>"><?= e(__('nav.categories')) ?></a>
            <a href="<?= e(url('/products')) ?>"><?= e(__('nav.products')) ?></a>
            <a href="<?= e(url('/cart')) ?>">
                <?= e(__('nav.cart')) ?>
                <span class="cart-count" id="cart-count"><?= (int) cart_count() ?></span>
            </a>

            <?php if (\App\Core\Auth::check()): ?>
                <?php if (\App\Core\Auth::roleIs(['admin', 'seller'])): ?>
                    <a href="<?= e(url('/admin')) ?>" class="nav-admin"><?= e(__('nav.admin')) ?></a>
                <?php endif; ?>
                <a href="<?= e(url('/account')) ?>"><?= e(\App\Core\Auth::user()['name'] ?? __('nav.account')) ?></a>
                <form method="post" action="<?= e(url('/logout')) ?>" class="nav-inline-form">
                    <?= \App\Core\Csrf::field() ?>
                    <button type="submit" class="link-button"><?= e(__('nav.logout')) ?></button>
                </form>
            <?php else: ?>
                <a href="<?= e(url('/login')) ?>"><?= e(__('nav.login')) ?></a>
                <a href="<?= e(url('/register')) ?>" class="nav-register"><?= e(__('nav.register')) ?></a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<?php foreach ($flash as $key => $item): ?>
    <div class="container">
        <div class="alert alert-<?= e($item['type'] ?? 'info') ?>" role="alert"><?= e($item['message']) ?></div>
    </div>
<?php endforeach; ?>

<main id="content">
    <?= $content ?>
</main>

<footer class="site-footer">
    <div class="container footer-inner">
        <div>
            <strong><?= e(env('APP_NAME', 'المتجر الليبي')) ?></strong>
            <p><?= e(__('footer.tagline')) ?></p>
        </div>
        <div class="footer-meta">
            <span><?= e(__('footer.currency')) ?>: <?= e(env('APP_CURRENCY_SYMBOL', 'د.ل')) ?></span>
            <span>&copy; <?= date('Y') ?> - <?= e(__('footer.rights')) ?></span>
        </div>
    </div>
</footer>

<script src="<?= e(asset('js/app.js')) ?>" defer></script>
</body>
</html>