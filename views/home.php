<section class="hero">
    <div class="container">
        <p class="hero-kicker">🇱🇾 متجر ليبي بالدينار الليبي</p>
        <h1><?= e(__('home.hero_title')) ?></h1>
        <p class="lead"><?= e(__('home.hero_text')) ?></p>
        <div class="hero-actions">
            <a class="btn btn-primary" href="<?= e(url('/products')) ?>"><?= e(__('home.hero_cta')) ?></a>
            <a class="btn btn-outline-light" href="<?= e(url('/categories')) ?>"><?= e(__('home.sections_title')) ?></a>
        </div>
    </div>
</section>

<section class="container section">
    <div class="section-head">
        <h2><?= e(__('home.sections_title')) ?></h2>
        <a href="<?= e(url('/categories')) ?>"><?= e(__('home.view_all')) ?> ←</a>
    </div>

    <div class="category-grid">
        <?php foreach ($categories as $category): ?>
            <a class="category-card" href="<?= e(url('category/' . $category['slug'])) ?>">
                <span class="category-emoji" style="background:<?= e($category['color']) ?>"><?= e($category['emoji']) ?></span>
                <span class="category-name"><?= e($category['name']) ?></span>
                <span class="category-count"><?= e(__('categories.count', ['count' => (string) $category['count']])) ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<section class="container section">
    <div class="section-head">
        <h2><?= e(__('home.featured_title')) ?></h2>
        <a href="<?= e(url('/products')) ?>"><?= e(__('home.view_all')) ?> ←</a>
    </div>

    <div class="product-grid">
        <?php foreach ($featured as $product): ?>
            <?= \App\Core\View::partial('partials/product-card', ['product' => $product]) ?>
        <?php endforeach; ?>
    </div>
</section>

<section class="trust-strip">
    <div class="container trust-grid">
        <div class="trust-item">
            <span class="trust-icon">🚚</span>
            <h3><?= e(__('home.trust_shipping_title')) ?></h3>
            <p><?= e(__('home.trust_shipping_text')) ?></p>
        </div>
        <div class="trust-item">
            <span class="trust-icon">💵</span>
            <h3><?= e(__('home.trust_pay_title')) ?></h3>
            <p><?= e(__('home.trust_pay_text')) ?></p>
        </div>
        <div class="trust-item">
            <span class="trust-icon">🔄</span>
            <h3><?= e(__('home.trust_returns_title')) ?></h3>
            <p><?= e(__('home.trust_returns_text')) ?></p>
        </div>
    </div>
</section>

<section class="container section">
    <div class="section-head">
        <h2><?= e(__('home.latest_title')) ?></h2>
        <a href="<?= e(url('/products?sort=newest')) ?>"><?= e(__('home.view_all')) ?> ←</a>
    </div>

    <div class="product-grid">
        <?php foreach ($latest as $product): ?>
            <?= \App\Core\View::partial('partials/product-card', ['product' => $product]) ?>
        <?php endforeach; ?>
    </div>
</section>