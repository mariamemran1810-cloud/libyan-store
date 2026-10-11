<section class="container section">
    <nav class="breadcrumb" aria-label="مسار التنقل">
        <a href="<?= e(url('/')) ?>"><?= e(__('breadcrumbs.home')) ?></a>
        <span aria-hidden="true">/</span>
        <span><?= e(__('admin.dashboard')) ?></span>
    </nav>

    <div class="page-head">
        <h1><?= e(__('admin.dashboard')) ?></h1>
        <p class="muted"><?= e(__('admin.role_you', ['role' => $role === 'admin' ? 'مسؤولة' : 'بائعة'])) ?></p>
    </div>

    <div class="admin-shell">
        <?= \App\Core\View::partial('admin/_nav', []) ?>

        <div class="admin-content">
            <?php if ($stats['lowStock'] > 0): ?>
                <div class="alert alert-error low-stock-banner">
                    <strong><?= e(__('admin.low_stock_banner', ['count' => (string) $stats['lowStock']])) ?></strong>
                    <ul>
                        <?php foreach ($lowStockItems as $item): ?>
                            <li><?= e($item['name']) ?> — <?= e(__('admin.stock_left', ['count' => (string) $item['stock']])) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="stat-grid">
                <a class="stat-card" href="<?= e(url('/admin/products')) ?>">
                    <span class="stat-value"><?= (int) $stats['products'] ?></span>
                    <span class="stat-label"><?= e(__('admin.stat_products')) ?></span>
                </a>
                <div class="stat-card">
                    <span class="stat-value"><?= e(format_price($stats['revenue'])) ?></span>
                    <span class="stat-label"><?= e(__('admin.stat_revenue')) ?></span>
                </div>
                <a class="stat-card" href="<?= e(url('/admin/orders')) ?>">
                    <span class="stat-value"><?= (int) $stats['orders'] ?></span>
                    <span class="stat-label"><?= e(__('admin.stat_orders')) ?></span>
                </a>
                <a class="stat-card" href="<?= e(url('/admin/orders')) ?>">
                    <span class="stat-value"><?= (int) $stats['pending'] ?></span>
                    <span class="stat-label"><?= e(__('admin.stat_pending')) ?></span>
                </a>
                <a class="stat-card" href="<?= e(url('/admin/products')) ?>">
                    <span class="stat-value"><?= (int) $stats['lowStock'] ?></span>
                    <span class="stat-label"><?= e(__('admin.stat_low_stock')) ?></span>
                </a>
                <a class="stat-card" href="<?= e(url('/admin/users')) ?>">
                    <span class="stat-value"><?= (int) $stats['users'] ?></span>
                    <span class="stat-label"><?= e(__('admin.stat_users')) ?></span>
                </a>
            </div>

            <div class="notice">
                <h2><?= e(__('admin.recent_orders')) ?></h2>
                <?php if ($recentOrders === []): ?>
                    <p class="muted"><?= e(__('admin.no_orders_yet')) ?></p>
                <?php else: ?>
                    <table class="table">
                        <thead>
                        <tr>
                            <th><?= e(__('account.order_no')) ?></th>
                            <th><?= e(__('admin.customer')) ?></th>
                            <th><?= e(__('account.order_total')) ?></th>
                            <th><?= e(__('account.order_status')) ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td>#<?= (int) $order['id'] ?></td>
                                <td><?= e($order['customer_name']) ?></td>
                                <td><?= e(format_price((float) $order['total'])) ?></td>
                                <td><?= e(\App\Models\OrderRepository::STATUS_LABELS[$order['status']] ?? $order['status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <a class="btn btn-outline" href="<?= e(url('/admin/orders')) ?>"><?= e(__('admin.view_all_orders')) ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>