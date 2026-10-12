<?php

declare(strict_types=1);

/**
 * اختبار سريع للأدوات الأساسية (Phase 1 smoke test).
 * التشغيل:  php docs/selftest.php
 */

define('BASE_PATH', dirname(__DIR__));

$_SERVER['REQUEST_METHOD'] = 'CLI';
$_SERVER['REQUEST_URI'] = '/';

require BASE_PATH . '/app/bootstrap.php';

use App\Core\Csrf;
use App\Core\Database;
use App\Core\Env;
use App\Core\Lang;
use App\Core\Session;
use App\Core\Throttle;
use App\Core\Validate;

$pass = 0;
$fail = 0;

function check(string $name, bool $ok, string $extra = ''): void
{
    global $pass, $fail;
    if ($ok) {
        $pass++;
        echo "[PASS] " . $name . PHP_EOL;
    } else {
        $fail++;
        echo "[FAIL] " . $name . ($extra !== '' ? ' | ' . $extra : '') . PHP_EOL;
    }
}

check('قراءة .env', Env::get('APP_NAME') !== '', Env::get('APP_NAME'));
check('تنسيق السعر بالدينار الليبي', format_price(12.5) === '12.500 د.ل', format_price(12.5));
check('ترجمة عربية', Lang::get('nav.home') === 'الرئيسية', Lang::get('nav.home'));
check('ترجمة بديلة عند غياب المفتاح', Lang::get('some.missing.key') === 'some.missing.key');

$errors = Validate::check([], ['email' => ['required'], 'name' => ['required']], ['email' => 'البريد', 'name' => 'الاسم']);
check('Validate: حقول مطلوبة فارغة تولّد خطأين', count($errors) === 2, json_encode($errors, JSON_UNESCAPED_UNICODE));

$errors = Validate::check(['email' => 'wrong', 'name' => 'سارة'], ['email' => ['required', 'email'], 'name' => ['required', 'min:2']]);
check('Validate: بريد خاطئ يولّد خطأ', isset($errors['email']));

$errors = Validate::check(['email' => 'test@example.com', 'name' => 'سارة'], ['email' => ['required', 'email'], 'name' => ['required', 'min:2']]);
check('Validate: بيانات صحيحة لا تولّد أخطاء', $errors === [], json_encode($errors, JSON_UNESCAPED_UNICODE));

check('slugify عربي', slugify('كتاب جميل') !== '' && !str_contains(slugify('كتاب جميل'), ' '), slugify('كتاب جميل'));
check('Csrf token بطول 64', strlen(Csrf::token()) === 64);

try {
    $pdo = Database::pdo();
    $pdo->exec('CREATE TABLE IF NOT EXISTS _selftest (id INTEGER PRIMARY KEY, note TEXT)');
    Database::insert('_selftest', ['note' => 'ok']);
    $row = Database::first('SELECT * FROM _selftest ORDER BY id DESC LIMIT 1');
    check('SQLite: إنشاء واستعلام وحقن مُعدّ', $row !== null && $row['note'] === 'ok');
} catch (Throwable $e) {
    check('SQLite: إنشاء واستعلام', false, $e->getMessage());
}

check('الروابط تبدأ بمقياس مائل', url('products') === '/products' && str_starts_with(asset('css/app.css'), '/assets/css/app.css'));
check('ملفات CSS تحمل رقم نسخة لتفادي الكاش', str_contains(asset('css/app.css'), '?v='));

// التحقق من تسجيل الأخطاء
\App\Core\Errors::log(new RuntimeException('selftest-log-entry'));
$log = @file_get_contents(BASE_PATH . '/storage/logs/app.log') ?: '';
check('تسجيل الأخطاء في السجل', str_contains($log, 'selftest-log-entry'));

// التحقق من صفحة 404 تعيد الحالة الصحيحة
$_SERVER['REQUEST_URI'] = '/definitely-missing-page';
ob_start();
\App\Core\Errors::notFoundPage();
$html = (string) ob_get_clean();
check('صفحة 404 عربية مع noindex', str_contains($html, 'الصفحة غير موجودة') && str_contains($html, 'noindex'));

// ===== المرحلة الثانية: البيانات التجريبية =====
use App\Models\ProductRepository;

$products = ProductRepository::products();
$categories = ProductRepository::categories();
$demoCategories = require BASE_PATH . '/database/demo_categories.php';
$demoProducts = require BASE_PATH . '/database/demo_products.php';
check('عدد الأقسام مطابق للبيانات التجريبية', count($categories) === count($demoCategories), count($categories) . ' ≠ ' . count($demoCategories));
check('عدد المنتجات مطابق للبيانات التجريبية', count($products) === count($demoProducts), count($products) . ' ≠ ' . count($demoProducts));

$slugs = array_column($products, 'slug');
check('روابط المنتجات فريدة', count($slugs) === count(array_unique($slugs)));
check('روابط الأقسام فريدة', count(array_unique(array_column($categories, 'slug'))) === count($categories));

$validCategories = array_column($categories, 'slug');
$okCategory = true;
$okPrice = true;
$okRequired = true;
foreach ($products as $p) {
    if (!in_array($p['category'], $validCategories, true)) {
        $okCategory = false;
    }
    if (!is_numeric($p['price']) || $p['price'] <= 0) {
        $okPrice = false;
    }
    foreach (['name', 'slug', 'tagline', 'description', 'emoji', 'color', 'created_at'] as $key) {
        if (($p[$key] ?? '') === '') {
            $okRequired = false;
        }
    }
}
check('كل منتج ينتمي إلى قسم صحيح', $okCategory);
check('كل الأسعار رقمية موجبة', $okPrice);
check('لا توجد حقول فارغة في المنتجات', $okRequired);
check('هناك منتجات مميزة', ProductRepository::featured(8) !== []);

$result = ProductRepository::search('قهوة', '', 'newest', 1, 12);
check('البحث يعيد نتائج', $result['total'] > 0, 'total=' . $result['total']);
$result = ProductRepository::search('', '', 'price_asc', 1, 12);
$prices = array_column($result['items'], 'price');
$sorted = $prices;
sort($sorted);
check('الفرز تصاعدي صحيح', $prices === $sorted);
$result = ProductRepository::search('', '', 'newest', 99, 12);
check('رقم الصفحة خارج النطاق يُصحَّح', $result['page'] === 1 || $result['page'] === $result['pages'], 'page=' . $result['page']);
check('منتج غير موجود يعيد null', ProductRepository::bySlug('no-such-product') === null);

// ===== المرحلة الثالثة: قاعدة البيانات والحسابات =====
$tables = array_column(Database::all("SELECT name FROM sqlite_master WHERE type='table'"), 'name');
$needed = ['categories', 'products', 'users', 'orders', 'order_items'];
$missing = array_diff($needed, $tables);
check('جداول قاعدة البيانات موجودة', $missing === [], implode(',', $missing));

check('منتجات قاعدة البيانات = البيانات التجريبية', (int) Database::scalar('SELECT COUNT(*) FROM products') === count($demoProducts));
check('كل المنتجات منشورة', (int) Database::scalar('SELECT COUNT(*) FROM products WHERE is_active = 0') === 0);

$roles = array_column(Database::all('SELECT DISTINCT role FROM users'), 'role');
sort($roles);
check('الأدوار الثلاثة موجودة', $roles === ['admin', 'customer', 'seller'], implode(',', $roles));

// لا توجد كلمة مرور محفوظة كنص صريح
$plain = Database::first("SELECT id FROM users WHERE password_hash NOT LIKE '\$2y\$%' AND password_hash NOT LIKE '\$argon%'");
check('كلمات المرور مشفّرة بـ bcrypt/argon', $plain === null);

// التحقق من كلمة مرور الحساب التجريبي
$admin = Database::first('SELECT password_hash FROM users WHERE email = ?', ['admin@example.com']);
check('password_verify يعمل على حساب المسؤولة', $admin !== null && password_verify('ChangeMe123!', $admin['password_hash']));

// ===== اختبار تقييد معدل الطلبات =====
Session::set('_throttle_demo', []);
check('المقيّد يسمح بالمحاولة الأولى', Throttle::attempt('demo', 5, 60) === true);
for ($i = 0; $i < 3; $i++) {
    Throttle::attempt('demo', 5, 60);
}
check('المقيّد يسمح بالمحاولة الخامسة', Throttle::attempt('demo', 5, 60) === true);
check('المقيّد يمنع المحاولة السادسة', Throttle::attempt('demo', 5, 60) === false);
Throttle::reset('demo');
check('إعادة الضبط تسمح من جديد', Throttle::attempt('demo', 5, 60) === true);
Throttle::reset('demo');

// ===== اختبار السلة (Session-based) =====
use App\Core\Cart;
use App\Exceptions\OutOfStockException;
use App\Models\OrderRepository;

Cart::clear();
check('السلة تبدأ فارغة', Cart::isEmpty());
check('إضافة منتج للسلة', Cart::add(1, 2));
check('عدد قطع السلة = 2', Cart::count() === 2, (string) Cart::count());
check('إجمالي السلة 370.000 د.ل', abs(Cart::subtotal() - 370.0) < 0.001, (string) Cart::subtotal());
Cart::add(1, 999);
check('الكمية مقيّدة بمخزون المنتج (12)', (Cart::items()[1]['qty'] ?? 0) === 12, (string) (Cart::items()[1]['qty'] ?? 0));
Cart::setQty(1, 0);
check('تصفير الكمية يحذف الصنف', Cart::isEmpty());
Cart::add(5, 1);
check('إضافة صنف آخر', Cart::count() === 1);
Cart::clear();
check('إفراغ السلة', Cart::isEmpty());

// ===== اختبار الطلبات والمخزون =====
$stock1 = (int) Database::scalar('SELECT stock FROM products WHERE id = 1');
$stock5 = (int) Database::scalar('SELECT stock FROM products WHERE id = 5');

$orderId = OrderRepository::create(
    ['name' => 'عميلة اختبار', 'phone' => '0900000000', 'city' => 'طرابلس', 'address' => 'عنوان تجريبي', 'notes' => 'اختبار'],
    [
        ['product_id' => 1, 'name' => 'قفطان', 'price' => 10.0, 'qty' => 2],
        ['product_id' => 5, 'name' => 'سماعات', 'price' => 5.5, 'qty' => 1],
    ]
);
$order = OrderRepository::find($orderId);
check('إنشاء طلب يحسب الإجمالي صحيحًا', $order !== null && abs((float) $order['total'] - 25.5) < 0.001, (string) ($order['total'] ?? 'null'));
check('بنود الطلب محفوظة', count(OrderRepository::items($orderId)) === 2);
check('الخصم من المخزون بعد الشراء', (int) Database::scalar('SELECT stock FROM products WHERE id = 1') === $stock1 - 2);
check('خصم كل صنف بمقداره', (int) Database::scalar('SELECT stock FROM products WHERE id = 5') === $stock5 - 1);
check('تحديث حالة الطلب', OrderRepository::updateStatus($orderId, 'confirmed') === 1);
check('حالة غير مسموحة تُرفض', OrderRepository::updateStatus($orderId, 'hack-status') === 0);

// محاولة شراء أكثر من المخزون => فشل وترتجع القاعدة كما كانت
$ordersBefore = (int) Database::scalar('SELECT COUNT(*) FROM orders');
$threw = false;
try {
    OrderRepository::create(
        ['name' => 'عميلة اختبار', 'phone' => '0900000000', 'city' => 'طرابلس', 'address' => 'عنوان تجريبي'],
        [['product_id' => 1, 'name' => 'قفطان', 'price' => 10.0, 'qty' => 9999]]
    );
} catch (OutOfStockException $e) {
    $threw = true;
}
check('شراء فوق المخزون يرمي استثناء', $threw);
check('الترجع يبقي المخزون كما كان', (int) Database::scalar('SELECT stock FROM products WHERE id = 1') === $stock1 - 2);
check('لا يُنشأ طلب عند الفشل', (int) Database::scalar('SELECT COUNT(*) FROM orders') === $ordersBefore);

Database::delete('orders', 'id = ?', [$orderId]);
check('حذف الطلب يحذف بنوده', (int) Database::scalar('SELECT COUNT(*) FROM order_items WHERE order_id = ?', [$orderId]) === 0);

// إعادة المخزون كما كان قبل الاختبار
Database::run('UPDATE products SET stock = ? WHERE id = ?', [$stock1, 1]);
Database::run('UPDATE products SET stock = ? WHERE id = ?', [$stock5, 5]);
check('المخزون رجع كما كان', (int) Database::scalar('SELECT stock FROM products WHERE id = 1') === $stock1);

// ===== اكتمال ملف اللغة العربية =====
$arLang = require BASE_PATH . '/lang/ar.php';
$usedKeys = [];
$viewsIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(BASE_PATH . '/views', FilesystemIterator::SKIP_DOTS));
foreach ($viewsIterator as $viewFile) {
    if (!$viewFile->isFile() || !str_ends_with($viewFile->getFilename(), '.php')) {
        continue;
    }
    $source = (string) file_get_contents($viewFile->getPathname());
    if (preg_match_all("/__\\(\\s*'([^']+)'/", $source, $found) > 0) {
        foreach ($found[1] as $key) {
            $usedKeys[$key] = $viewFile->getFilename();
        }
    }
}
$missingKeys = array_values(array_diff(array_keys($usedKeys), array_keys($arLang)));
check('كل مفاتيح الترجمة في القوالب موجودة في lang/ar.php', $missingKeys === [], implode(', ', $missingKeys));
check('عدد نصوص العربية كافٍ (200+ نص)', count($arLang) >= 200, (string) count($arLang));

// الإنجليزية: أي مفتاح غير مترجم يرجع إلى النص العربي تلقائيًا
\App\Core\Lang::set('en');
$englishFallback = \App\Core\Lang::get('nav.cart');
$englishSpecific = \App\Core\Lang::get('buttons.add_to_cart');
\App\Core\Lang::set('ar');
check('الإنجليزية: مفتاح مترجم يظهر بالإنجليزية', $englishFallback !== '', $englishFallback);
check('الإنجليزية: المفتاح غير المترجم يرجع للعربية', $englishSpecific !== '' && !str_contains($englishSpecific, 'buttons.'), $englishSpecific);

// ملف .env خارج Git
$ignore = (string) file_get_contents(BASE_PATH . '/.gitignore');
check('ملف .env مُستثنى من Git', str_contains($ignore, '.env'));

// لا توجد كلمات مرور مكتوبة داخل كود المشروع
$dangerous = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(BASE_PATH . '/app'));
foreach ($iterator as $file) {
    if ($file->isFile() && str_ends_with($file->getFilename(), '.php')) {
        $code = (string) file_get_contents($file->getPathname());
        if (preg_match('/(password|secret|api_key)\s*=\s*[\'"][^\'"]{6,}/i', $code)) {
            $dangerous[] = $file->getFilename();
        }
    }
}
check('لا مفاتيح سرية داخل كود app/', $dangerous === [], implode(',', $dangerous));

// ===== رسوم التوصيل حسب المدينة =====
$tables = array_column(Database::all("SELECT name FROM sqlite_master WHERE type = 'table'"), 'name');
check('جدول الإعدادات موجود', in_array('settings', $tables, true));

$cities = \App\Models\SettingRepository::deliveryCities();
check('مدن التوصيل مُقرأة من الإعدادات', count($cities) >= 3, count($cities) . ' مدينة');
check('رسم طرابلس = 5.000', abs((\App\Models\SettingRepository::deliveryFeeFor('طرابلس') ?? -1.0) - 5.0) < 0.001);
check('مدينة غير مدرجة ترجع null', \App\Models\SettingRepository::deliveryFeeFor('مدينة غير موجودة') === null);
check('ملاحظة التوصيل وهاتف المتجر محفوظان', \App\Models\SettingRepository::deliveryNote() !== '' && \App\Models\SettingRepository::storePhone() !== '');

$origCitiesValue = \App\Models\SettingRepository::get('delivery_cities');
\App\Models\SettingRepository::setDeliveryCities(['مصراتة' => 6.25, 'سرت' => 0]);
$roundTrip = \App\Models\SettingRepository::deliveryCities();
check('حفظ مدن التوصيل يكتب صيغة صحيحة', count($roundTrip) === 2
    && abs($roundTrip[0]['fee'] - 6.25) < 0.001
    && abs((\App\Models\SettingRepository::deliveryFeeFor('مصراتة') ?? -1.0) - 6.25) < 0.001);
\App\Models\SettingRepository::put('delivery_cities', $origCitiesValue);
check('استعادة إعداد المدن الأصلي', abs((\App\Models\SettingRepository::deliveryFeeFor('طرابلس') ?? -1.0) - 5.0) < 0.001);

$ordersCols = array_column(Database::all('PRAGMA table_info(orders)'), 'name');
check('عمود delivery_fee موجود في الطلبات', in_array('delivery_fee', $ordersCols, true));
check('الطلبات بلا رسوم كامنة (افتراضي 0)', (int) Database::scalar('SELECT COUNT(*) FROM orders WHERE delivery_fee != 0') === 0);

// ===== مدة التحضير =====
$productCols = array_column(Database::all('PRAGMA table_info(products)'), 'name');
check('عمود prep_minutes موجود في المنتجات', in_array('prep_minutes', $productCols, true));
$withPrep = (int) Database::scalar('SELECT COUNT(*) FROM products WHERE prep_minutes > 0');
check('12 منتجًا غذائيًا له مدة تحضير', $withPrep === 12, (string) $withPrep);
$foodPrepOk = (int) Database::scalar(
    "SELECT COUNT(*) FROM products p JOIN categories c ON c.id = p.category_id
     WHERE c.slug IN ('cafes', 'desserts', 'restaurants', 'fastfood') AND p.prep_minutes > 0"
) === 12;
check('جميع أطعمة ومشروبات لها مدة تحضير', $foodPrepOk);
$dressPrep = (int) Database::scalar(
    "SELECT COUNT(*) FROM products p JOIN categories c ON c.id = p.category_id
     WHERE c.slug LIKE 'fashion%' AND p.prep_minutes > 0"
);
check('الملابس بلا مدة تحضير', $dressPrep === 0, (string) $dressPrep);

// نصوص اللغة الجديدة موجودة
check('نصوص الإعدادات ورسوم التوصيل مكتملة',
    isset($arLang['admin.settings'], $arLang['admin.f_delivery_cities'], $arLang['checkout.fee_by_city'], $arLang['product.prep_time']));

// ===== حماية من مشكلة حساسية الأحرف (تنكسر على لينكس فقط) =====
$mwOk = true;
$mwBad = [];
foreach (glob(BASE_PATH . '/app/Middleware/*.php') ?: [] as $mwFile) {
    $mwSource = (string) file_get_contents($mwFile);
    $mwExpected = basename($mwFile, '.php');
    if (preg_match('/namespace\s+([^;]+);/', $mwSource, $mwNs)
        && preg_match('/(?:final\s+)?class\s+(\w+)/', $mwSource, $mwClass)) {
        if ($mwClass[1] !== $mwExpected || trim($mwNs[1]) !== 'App\\Middleware') {
            $mwOk = false;
            $mwBad[] = $mwExpected . '≠' . $mwClass[1];
        }
    }
}
check('اسم ملف الوسائط = اسم الصفوف بالضبط (حساس للأحرف)', $mwOk, implode(',', $mwBad));

// كل وسيلة مذكورة في المسارات قابلة للاستدعاء بالحالة الصحيحة
$routesSource = (string) file_get_contents(BASE_PATH . '/app/routes.php');
preg_match_all("/'(auth|guest|role:[a-z]+)'/", $routesSource, $mwFound);
$mwResolved = true;
foreach (array_unique($mwFound[1]) as $mwName) {
    $mwKey = explode(':', $mwName, 2)[0];
    if (!class_exists('App\\Middleware\\' . ucfirst($mwKey) . 'Middleware')) {
        $mwResolved = false;
    }
}
check('كل وسائط المسارات تُحلّ إلى صفوف موجودة', $mwResolved && $mwFound[1] !== [], implode(',', array_unique($mwFound[1])));

// ===== جاهزية الإنتاج =====
// 1) متغيرات بيئة التشغيل الحقيقية تتفوق على ملف .env (Render / VPS / cPanel)
putenv('SELFTEST_ENV_CASE=قيمة من البيئة');
check('متغير البيئة يتفوق على ملف .env', Env::get('SELFTEST_ENV_CASE', 'افتراضي') === 'قيمة من البيئة');
putenv('SELFTEST_ENV_CASE');
check('المفتاح الغائب يرجع القيمة الافتراضية', Env::get('SELFTEST_ENV_CASE', 'افتراضي') === 'افتراضي');
putenv('SELFTEST_BOOL_CASE=false');
check('Env::bool يقرأ من البيئة', Env::bool('SELFTEST_BOOL_CASE', true) === false);
putenv('SELFTEST_BOOL_CASE');

// 2) انتظار عند التزامن (يمنع خطأ "database is locked" على خادم ويب متعدد الطلبات)
check('SQLite ينتظر حتى ينتهي المقفل (busy_timeout = 5000)', (int) Database::scalar('PRAGMA busy_timeout') === 5000);

// 3) تدوير سجل الأخطاء حتى لا يكبر بلا حد
$tmpLog = sys_get_temp_dir() . '/selftest-log-rotate.log';
$tmpLogOld = $tmpLog . '.1';
@unlink($tmpLog);
@unlink($tmpLogOld);
file_put_contents($tmpLog, str_repeat('x', 2_100_000));
\App\Core\Errors::log(new \RuntimeException('selftest-rotate'), $tmpLog);
check('تدوير السجل عند تجاوز 2MB', is_file($tmpLogOld) && is_file($tmpLog) && filesize($tmpLog) < 2_000_000);
@unlink($tmpLog);
@unlink($tmpLogOld);

echo PHP_EOL . 'النتيجة: ' . $pass . ' ناجح، ' . $fail . ' فاشل' . PHP_EOL;
exit($fail > 0 ? 1 : 0);