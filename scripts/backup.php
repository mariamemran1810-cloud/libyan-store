<?php

declare(strict_types=1);

/**
 * نسخة احتياطية بسيطة وآمنة:
 * - قاعدة البيانات (SQLite)
 * - الصور المرفوعة (storage/uploads)
 *
 * التشغيل:  php scripts/backup.php
 * الناتج:   backups/2026-10-11_153000/  (مجلد جاهز للتنزيل)
 *
 * ملاحظة: مجلد backups/ خارج الويب ولا يمكن الوصول إليه من المتصفح.
 */

if (PHP_SAPI !== 'cli') {
    exit("cli only\n");
}

define('BASE_PATH', dirname(__DIR__));
$_SERVER['REQUEST_METHOD'] = 'CLI';
$_SERVER['REQUEST_URI'] = '/';
require BASE_PATH . '/app/bootstrap.php';

use App\Core\Database;
use App\Core\Env;

$stamp = date('Y-m-d_His');
$destination = BASE_PATH . '/backups/' . $stamp;

if (!is_dir($destination) && !@mkdir($destination, 0775, true)) {
    exit("تعذّر إنشاء مجلد النسخة الاحتياطية: $destination\n");
}

// 1) قاعدة البيانات (SQLite فقط — مع MySQL يكون النسخ من لوحة الخادم)
if (Env::get('DB_DRIVER', 'sqlite') !== 'sqlite') {
    echo "تنبيه: قاعدة البيانات MySQL — استخدمي نسخة mysqldump من لوحة الاستضافة.\n";
    exit(0);
}

$dbFile = BASE_PATH . '/' . Env::get('DB_DATABASE', 'database/database.sqlite');
if (!is_file($dbFile)) {
    exit("لم أجد قاعدة البيانات: $dbFile\n");
}

// ضمان اكتمال الكتابة قبل النسخ
Database::pdo()->exec('PRAGMA wal_checkpoint(TRUNCATE)');

if (!copy($dbFile, $destination . '/database.sqlite')) {
    exit("تعذّر نسخ قاعدة البيانات.\n");
}

// 2) الصور المرفوعة
$uploadsSource = BASE_PATH . '/storage/uploads';
$uploadsTarget = $destination . '/uploads';
$filesCount = 0;
$bytesCount = (int) filesize($dbFile);

if (is_dir($uploadsSource)) {
    if (!mkdir($uploadsTarget, 0775, true) && !is_dir($uploadsTarget)) {
        exit("تعذّر إنشاء مجلد الصور في النسخة.\n");
    }

    foreach (glob($uploadsSource . '/*') ?: [] as $file) {
        if (is_file($file) && copy($file, $uploadsTarget . '/' . basename($file))) {
            $filesCount++;
            $bytesCount += (int) filesize($file);
        }
    }
}

// 3) ملخص صغير داخل النسخة
$summary = "نسخة احتياطية للمتجر الليبي\n"
    . 'التاريخ: ' . date('Y-m-d H:i:s') . "\n"
    . 'المنتجات: ' . Database::scalar('SELECT COUNT(*) FROM products') . "\n"
    . 'الطلبات: ' . Database::scalar('SELECT COUNT(*) FROM orders') . "\n"
    . 'المستخدمون: ' . Database::scalar('SELECT COUNT(*) FROM users') . "\n"
    . 'الصور: ' . $filesCount . "\n";

file_put_contents($destination . '/SUMMARY.txt', $summary);

echo "تمت النسخة الاحتياطية بنجاح ✅\n";
echo 'المجلد: backups/' . $stamp . "\n";
echo $summary;
echo 'الحجم الإجمالي: ' . number_format($bytesCount / 1024, 1) . " كيلوبايت\n";
