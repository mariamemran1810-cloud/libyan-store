<?php

declare(strict_types=1);

/**
 * إعادة زرع البيانات التجريبية (الأقسام والمنتجات) من الملفات:
 *   database/demo_categories.php
 *   database/demo_products.php
 *
 * مفيد عندما تغيّر محتوى الملفات وتريدين تحديث قاعدة البيانات.
 * - يحافظ على حسابات المستخدمين.
 * - يرفض التنفيذ إذا وُجدت طلبات حقيقية (حماية من فقدان بيانات).
 *
 * التشغيل:  php scripts/reseed-demo.php
 */

if (PHP_SAPI !== 'cli') {
    exit("cli only\n");
}

define('BASE_PATH', dirname(__DIR__));
$_SERVER['REQUEST_METHOD'] = 'CLI';
$_SERVER['REQUEST_URI'] = '/';
require BASE_PATH . '/app/bootstrap.php';

use App\Core\Database;

$orders = (int) Database::scalar('SELECT COUNT(*) FROM orders');
if ($orders > 0) {
    exit("توجد $orders طلبًا في القاعدة. هذا السكربت يستبدل المنتجات والأقسام فقط،\n"
        . "ولذا أوقفته لحماية بيانات العميلات. احذفي الطلبات أولًا إن كانت تجريبية.\n");
}

$products = (int) Database::scalar('SELECT COUNT(*) FROM products');
$categories = (int) Database::scalar('SELECT COUNT(*) FROM categories');
$users = (int) Database::scalar('SELECT COUNT(*) FROM users');

echo "الوضع الحالي: $products منتج، $categories قسم، $users مستخدم.\n";

// حذف البيانات القديمة (الطلبات صفر مؤكد أعلاه)
Database::run('DELETE FROM order_items');
Database::run('DELETE FROM products');
Database::run('DELETE FROM categories');

// إعادة ترقيم المعرّفات لتبدأ من 1 (مثل البيانات الأصلية)
try {
    Database::run('DELETE FROM sqlite_sequence');
} catch (\Throwable) {
    // بعض المخططات لا تستخدم AUTOINCREMENT — لا مشكلة
}

echo "تم حذف الأقسام والمنتجات القديمة.\n";

// إعادة الزرع عبر migrate.php (يملأ ما هو فارغ فقط)
$command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(BASE_PATH . '/scripts/migrate.php');
passthru($command, $exitCode);

if ($exitCode !== 0) {
    exit("فشلت إعادة الزرع (رمز: $exitCode).\n");
}

echo "\n=== بعد التحديث ===\n";
echo 'الأقسام: ' . Database::scalar('SELECT COUNT(*) FROM categories') . "\n";
echo 'المنتجات: ' . Database::scalar('SELECT COUNT(*) FROM products') . "\n";
echo 'المستخدمون: ' . Database::scalar('SELECT COUNT(*) FROM users') . "\n";
