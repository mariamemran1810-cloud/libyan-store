<?php

declare(strict_types=1);

/**
 * إنشاء قاعدة البيانات وزرع البيانات التجريبية.
 *
 * الاستخدام:
 *   php scripts/migrate.php                 <- إنشاء المخطط + بيانات تجريبية
 *   php scripts/migrate.php --fresh         <- حذف وإعادة إنشاء كاملة (احترسي!)
 *
 * لإنشاء حساب مسؤول (بدون كلمة مرور داخل الكود):
 *   php scripts/migrate.php --admin="you@example.com" --password="كلمة-سرية"
 * أو ضعي في .env:
 *   ADMIN_EMAIL=you@example.com
 *   ADMIN_PASSWORD=كلمة-سرية
 */

if (PHP_SAPI !== 'cli') {
    exit("هذا الملف يعمل من سطر الأوامر فقط.\n");
}

define('BASE_PATH', dirname(__DIR__));
$_SERVER['REQUEST_METHOD'] = 'CLI';
$_SERVER['REQUEST_URI'] = '/';

require BASE_PATH . '/app/bootstrap.php';

use App\Core\Database;
use App\Core\Env;

$options = getopt('', ['fresh', 'admin:', 'password:']);

echo "==> إنشاء المخطط...\n";
$fresh = array_key_exists('fresh', $options);

if ($fresh) {
    $path = BASE_PATH . '/' . Env::get('DB_DATABASE', 'database/database.sqlite');
    foreach ([$path, $path . '-wal', $path . '-shm', $path . '-journal'] as $file) {
        if (is_file($file)) {
            unlink($file);
            echo "    تم حذف: " . basename($file) . "\n";
        }
    }
}

$sql = file_get_contents(BASE_PATH . '/database/schema.sql');
if ($sql === false) {
    exit("تعذّر قراءة database/schema.sql\n");
}
Database::pdo()->exec($sql);
echo "    المخطط جاهز.\n";

// ===== ترقيات المخطط للقواعد القديمة (آمنة ومتكررة) =====
$existing = array_column(Database::all('PRAGMA table_info(products)'), 'name');
if (!in_array('prep_minutes', $existing, true)) {
    Database::run('ALTER TABLE products ADD COLUMN prep_minutes INTEGER NOT NULL DEFAULT 0');
    echo "    ترقية: عمود prep_minutes (مدة التحضير).\n";
}

$existingOrders = array_column(Database::all('PRAGMA table_info(orders)'), 'name');
if (!in_array('delivery_fee', $existingOrders, true)) {
    Database::run('ALTER TABLE orders ADD COLUMN delivery_fee REAL NOT NULL DEFAULT 0');
    echo "    ترقية: عمود delivery_fee (رسوم التوصيل).\n";
}

// ===== زرع الأقسام =====
$categories = require BASE_PATH . '/database/demo_categories.php';
if ((int) Database::scalar('SELECT COUNT(*) FROM categories') === 0) {
    foreach ($categories as $category) {
        Database::insert('categories', [
            'name' => $category['name'],
            'slug' => $category['slug'],
            'emoji' => $category['emoji'],
            'color' => $category['color'],
            'description' => $category['description'],
            'position' => $category['id'],
        ]);
    }
    echo "==> زُرعت " . count($categories) . " أقسام.\n";
} else {
    echo "==> الأقسام موجودة مسبقًا (تخطّي).\n";
}

// ===== زرع المنتجات =====
if ((int) Database::scalar('SELECT COUNT(*) FROM products') === 0) {
    $categoryIds = [];
    foreach (Database::all('SELECT id, slug FROM categories') as $row) {
        $categoryIds[$row['slug']] = (int) $row['id'];
    }

    $products = require BASE_PATH . '/database/demo_products.php';
    foreach ($products as $product) {
        Database::insert('products', [
            'category_id' => $categoryIds[$product['category']] ?? 1,
            'name' => $product['name'],
            'slug' => $product['slug'],
            'price' => $product['price'],
            'old_price' => $product['old_price'],
            'stock' => $product['stock'],
            'prep_minutes' => $product['prep_minutes'] ?? 0,
            'emoji' => $product['emoji'],
            'color' => $product['color'],
            'tagline' => $product['tagline'],
            'description' => $product['description'],
            'features' => json_encode($product['features'], JSON_UNESCAPED_UNICODE),
            'featured' => $product['featured'] ? 1 : 0,
            'is_active' => 1,
            'created_at' => $product['created_at'] . ' 00:00:00',
        ]);
    }
    echo "==> زُرع " . count($products) . " منتجات تجريبية.\n";
} else {
    echo "==> المنتجات موجودة مسبقًا (تخطّي).\n";
}

// ===== حسابات تجريبية للتجربة (كلمات مرور من طرفك فقط) =====
$accounts = [
    ['name' => 'مسؤولة المتجر', 'email' => 'admin@example.com', 'role' => 'admin'],
    ['name' => 'البائعة نورة', 'email' => 'seller@example.com', 'role' => 'seller'],
    ['name' => 'العميلة سارة', 'email' => 'customer@example.com', 'role' => 'customer'],
];

$defaultPassword = 'ChangeMe123!';
$createdAny = false;
foreach ($accounts as $account) {
    $exists = Database::first('SELECT id FROM users WHERE email = ?', [$account['email']]);
    if ($exists !== null) {
        continue;
    }
    Database::insert('users', [
        'name' => $account['name'],
        'email' => $account['email'],
        'password_hash' => password_hash($defaultPassword, PASSWORD_DEFAULT),
        'role' => $account['role'],
    ]);
    $createdAny = true;
    echo "==> أُنشئ حساب (" . $account['role'] . "): " . $account['email'] . "\n";
}
if ($createdAny) {
    echo "    كلمة المرور الافتراضية لهذه الحسابات: " . $defaultPassword . "\n";
    echo "    غيّريها فورًا، أو أنشئي حسابك الخاص بالأمر أدناه.\n";
}

// ===== حساب مسؤول مخصص (لا كلمة مرور داخل الكود) =====
$adminEmail = (string) ($options['admin'] ?? Env::get('ADMIN_EMAIL', ''));
$adminPassword = (string) ($options['password'] ?? Env::get('ADMIN_PASSWORD', ''));

if ($adminEmail !== '' && $adminPassword !== '') {
    $exists = Database::first('SELECT id FROM users WHERE email = ?', [$adminEmail]);
    if ($exists === null) {
        Database::insert('users', [
            'name' => 'مسؤول المتجر',
            'email' => $adminEmail,
            'password_hash' => password_hash($adminPassword, PASSWORD_DEFAULT),
            'role' => 'admin',
        ]);
        echo "==> أُنشئ حساب مسؤول مخصص: " . $adminEmail . "\n";
    } else {
        Database::run('UPDATE users SET password_hash = ?, role = ? WHERE email = ?', [
            password_hash($adminPassword, PASSWORD_DEFAULT),
            'admin',
            $adminEmail,
        ]);
        echo "==> تم تحديث كلمة مرور المسؤول: " . $adminEmail . "\n";
    }
} else {
    echo "==> لإنشاء حساب مسؤول مخصص:\n";
    echo "    php scripts/migrate.php --admin=\"you@example.com\" --password=\"سرّك\"\n";
}

echo "\nتم بنجاح. عدد المنتجات الآن: " . (int) Database::scalar('SELECT COUNT(*) FROM products')
    . " | المستخدمون: " . (int) Database::scalar('SELECT COUNT(*) FROM users') . "\n";