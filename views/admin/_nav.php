<?php

/** قائمة جانبية للوحة الإدارة. المتغير المتوقع: المسار الحالي من $_SERVER */
$current = (string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);
$items = [
    ['/admin', 'لوحة التحكم', '📊'],
    ['/admin/products', 'المنتجات', '📦'],
    ['/admin/orders', 'الطلبات', '🧾'],
];
if (\App\Core\Auth::roleIs('admin')) {
    $items[] = ['/admin/categories', 'الأقسام', '🗂️'];
    $items[] = ['/admin/users', 'المستخدمون', '👥'];
    $items[] = ['/admin/settings', 'الإعدادات', '⚙️'];
}
?>
<aside class="admin-nav" aria-label="قائمة الإدارة">
    <?php foreach ($items as [$href, $label, $icon]): ?>
        <a class="admin-nav-link <?= $current === $href ? 'active' : '' ?>" href="<?= e(url($href)) ?>">
            <span aria-hidden="true"><?= $icon ?></span> <?= e($label) ?>
        </a>
    <?php endforeach; ?>
</aside>