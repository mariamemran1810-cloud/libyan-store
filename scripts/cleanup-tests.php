<?php

declare(strict_types=1);

/**
 * تنظيف بيانات الاختبار بعد التجربة:
 * - حذف الطلبات التجريبية
 * - إعادة المخزون لقيمته من البيانات التجريبية
 * - حذف الصور المرفوعة التي لم تعد مرتبطة بمنتج
 *
 * التشغيل: php scripts/cleanup-tests.php
 */

if (PHP_SAPI !== 'cli') {
    exit("cli only\n");
}

define('BASE_PATH', dirname(__DIR__));
$_SERVER['REQUEST_METHOD'] = 'CLI';
$_SERVER['REQUEST_URI'] = '/';
require BASE_PATH . '/app/bootstrap.php';

use App\Core\Database;

// 1) حذف الطلبات التجريبية
$deleted = Database::delete('orders', "customer_name LIKE '%اختبار%'");
echo "deleted test orders: $deleted\n";
Database::run('DELETE FROM order_items WHERE order_id NOT IN (SELECT id FROM orders)');

// 2) إعادة المخزون كما في demo_products.php
$demo = require BASE_PATH . '/database/demo_products.php';
foreach ($demo as $product) {
    Database::run('UPDATE products SET stock = ? WHERE slug = ?', [(int) $product['stock'], $product['slug']]);
}
echo "stock restored for " . count($demo) . " products\n";

// 3) حذف الصور اليتيمة
$dir = BASE_PATH . '/storage/uploads';
foreach (glob($dir . '/*.{png,jpg,jpeg,webp,gif}', GLOB_BRACE) as $file) {
    if (Database::first('SELECT id FROM products WHERE image = ?', [basename($file)]) === null) {
        unlink($file);
        echo "removed orphan upload: " . basename($file) . "\n";
    }
}

echo "=> products: " . Database::scalar('SELECT COUNT(*) FROM products')
    . " | orders: " . Database::scalar('SELECT COUNT(*) FROM orders')
    . " | stock(id=1): " . Database::scalar('SELECT stock FROM products WHERE id = 1') . "\n";