<section class="container section">
    <nav class="breadcrumb" aria-label="مسار التنقل">
        <a href="<?= e(url('/')) ?>"><?= e(__('breadcrumbs.home')) ?></a>
        <span aria-hidden="true">/</span>
        <span><?= e(__('categories.title')) ?></span>
    </nav>

    <div class="page-head">
        <h1><?= e(__('categories.title')) ?></h1>
        <p class="muted"><?= e(__('categories.intro')) ?></p>
    </div>

    <div class="category-grid wide">
        <?php foreach ($categories as $category): ?>
            <a class="category-card" href="<?= e(url('category/' . $category['slug'])) ?>">
                <span class="category-emoji" style="background:<?= e($category['color']) ?>"><?= e($category['emoji']) ?></span>
                <span class="category-name"><?= e($category['name']) ?></span>
                <span class="category-desc"><?= e($category['description']) ?></span>
                <span class="category-count"><?= e(__('categories.count', ['count' => (string) $category['count']])) ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</section>