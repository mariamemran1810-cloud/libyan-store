<?php

declare(strict_types=1);

/**
 * اختبار HTTP شامل (المرحلة السادسة).
 *
 * يشغّل خادمًا مؤقتًا على منفذ مستقل، ينفّذ سيناريوهات حقيقية بالمتصفح
 * (صفحات، تسجيل، سلة، طلب، صلاحيات، أمان)، ثم ينظف كل بيانات الاختبار.
 *
 * التشغيل:  php docs/httptest.php
 */

if (PHP_SAPI !== 'cli') {
    exit("cli only\n");
}

define('BASE_PATH', dirname(__DIR__));
$_SERVER['REQUEST_METHOD'] = 'CLI';
$_SERVER['REQUEST_URI'] = '/';
require BASE_PATH . '/app/bootstrap.php';

use App\Core\Database;

// ======================================================================
//  أدوات الاختبار
// ======================================================================

$GLOBALS['__passed'] = 0;
$GLOBALS['__failed'] = 0;
$GLOBALS['__fails'] = [];

function check(string $label, bool $ok, string $detail = ''): void
{
    if ($ok) {
        $GLOBALS['__passed']++;
        echo "[PASS] $label\n";

        return;
    }

    $GLOBALS['__failed']++;
    $GLOBALS['__fails'][] = $label;
    echo '[FAIL] ' . $label . ($detail !== '' ? ' — ' . $detail : '') . "\n";
}

function token_of(string $html): string
{
    $m = preg_match('/name="_token" value="([a-f0-9]+)"/', $html, $matches);

    return $m === 1 ? $matches[1] : '';
}

/** عميل HTTP بسيط يحتفظ بملفات الارتباط (بدون أي مكتبات خارجية). */
final class Http
{
    /** @var array<string,string> */
    private array $cookies = [];

    public function __construct(
        private int $port,
        private string $host = '127.0.0.1'
    ) {
    }

    public function forgetCookies(): void
    {
        $this->cookies = [];
    }

    public function get(string $path): array
    {
        return $this->send('GET', $path);
    }

    public function head(string $path): array
    {
        return $this->send('HEAD', $path);
    }

    public function post(string $path, array $data = [], string $referer = ''): array
    {
        return $this->send('POST', $path, $data, $referer);
    }

    /** @param array<int,array{name:string,filename:string,type:string,content:string}> $files */
    public function postMultipart(string $path, array $data, array $files, string $referer = ''): array
    {
        $boundary = '----httptest' . bin2hex(random_bytes(8));
        $body = '';

        foreach ($data as $key => $value) {
            $body .= "--$boundary\r\n";
            $body .= 'Content-Disposition: form-data; name="' . $key . "\"\r\n\r\n";
            $body .= (string) $value . "\r\n";
        }

        foreach ($files as $file) {
            $body .= "--$boundary\r\n";
            $body .= 'Content-Disposition: form-data; name="' . $file['name'] . '"; filename="' . $file['filename'] . "\"\r\n";
            $body .= 'Content-Type: ' . $file['type'] . "\r\n\r\n";
            $body .= $file['content'] . "\r\n";
        }

        $body .= "--$boundary--\r\n";

        $headers = ['Content-Type' => 'multipart/form-data; boundary=' . $boundary];

        return $this->send('POST', $path, [], $referer, $body, $headers);
    }

    /** @return array{status:int,headers:array<string,string>,body:string} */
    private function send(
        string $method,
        string $path,
        array $data = [],
        string $referer = '',
        string $rawBody = '',
        array $headers = []
    ): array {
        $body = $rawBody;
        if ($method === 'POST' && $rawBody === '') {
            $body = http_build_query($data, '', '&');
        }

        $request = $method . ' ' . $path . " HTTP/1.1\r\n";
        $request .= 'Host: ' . $this->host . ':' . $this->port . "\r\n";
        $request .= "Connection: close\r\n";
        $request .= "Accept: text/html,application/xhtml+xml,*/*\r\n";
        $request .= "User-Agent: LibreStore-HTTP-Test\r\n";

        if ($this->cookies !== []) {
            $pairs = [];
            foreach ($this->cookies as $key => $value) {
                $pairs[] = $key . '=' . $value;
            }
            $request .= 'Cookie: ' . implode('; ', $pairs) . "\r\n";
        }

        if ($referer !== '') {
            $request .= 'Referer: ' . $referer . "\r\n";
        }

        if ($method === 'POST') {
            $request .= 'Content-Length: ' . strlen($body) . "\r\n";
            if (!isset($headers['Content-Type'])) {
                $headers['Content-Type'] = 'application/x-www-form-urlencoded';
            }
        }

        foreach ($headers as $name => $value) {
            $request .= $name . ': ' . $value . "\r\n";
        }

        $request .= "\r\n" . $body;

        $fp = @fsockopen($this->host, $this->port, $errno, $errstr, 5);
        if ($fp === false) {
            throw new RuntimeException('تعذّر الاتصال بالخادم: ' . $errstr);
        }

        stream_set_timeout($fp, 15);
        fwrite($fp, $request);

        $raw = '';
        while (!feof($fp)) {
            $chunk = fread($fp, 8192);
            if ($chunk === false || $chunk === '') {
                break;
            }
            $raw .= $chunk;
        }
        fclose($fp);

        $split = strpos($raw, "\r\n\r\n");
        if ($split === false) {
            return ['status' => 0, 'headers' => [], 'body' => $raw];
        }

        $head = substr($raw, 0, $split);
        $content = substr($raw, $split + 4);
        $lines = explode("\r\n", $head);

        $status = (int) (explode(' ', $lines[0] ?? '')[1] ?? 0);
        $respHeaders = [];

        foreach (array_slice($lines, 1) as $line) {
            $colon = strpos($line, ':');
            if ($colon === false) {
                continue;
            }
            $name = strtolower(trim(substr($line, 0, $colon)));
            $value = trim(substr($line, $colon + 1));
            $respHeaders[$name] = isset($respHeaders[$name]) ? $respHeaders[$name] . ', ' . $value : $value;

            if ($name === 'set-cookie') {
                $pair = explode(';', $value)[0];
                $equals = strpos($pair, '=');
                if ($equals !== false) {
                    $this->cookies[trim(substr($pair, 0, $equals))] = trim(substr($pair, $equals + 1));
                }
            }
        }

        if (isset($respHeaders['transfer-encoding']) && stripos($respHeaders['transfer-encoding'], 'chunked') !== false) {
            $content = $this->dechunk($content);
        }

        return ['status' => $status, 'headers' => $respHeaders, 'body' => $content];
    }

    private function dechunk(string $data): string
    {
        $out = '';
        $offset = 0;

        while ($offset < strlen($data)) {
            $lineEnd = strpos($data, "\r\n", $offset);
            if ($lineEnd === false) {
                break;
            }
            $sizeHex = trim(substr($data, $offset, $lineEnd - $offset));
            $size = (int) hexdec(explode(';', $sizeHex)[0]);
            if ($size === 0) {
                break;
            }
            $out .= substr($data, $lineEnd + 2, $size);
            $offset = $lineEnd + 2 + $size + 2;
        }

        return $out;
    }
}

// ======================================================================
//  التهيئة: قاعدة البيانات + خادم مؤقت
// ======================================================================

$startedAt = time();

$productCount = (int) Database::scalar('SELECT COUNT(*) FROM products');
$userCount = (int) Database::scalar('SELECT COUNT(*) FROM users');

if ($productCount === 0 || $userCount === 0) {
    echo "قاعدة البيانات غير جاهزة. شغّلي أولاً:  php scripts/migrate.php\n";
    exit(1);
}

$port = (int) (getenv('HTTP_TEST_PORT') ?: 8193);
$logFile = BASE_PATH . '/storage/logs/httptest-server.log';
$appLogPath = BASE_PATH . '/storage/logs/app.log';
$logStartSize = is_file($appLogPath) ? (int) filesize($appLogPath) : 0;
@mkdir(dirname($logFile), 0775, true);
@unlink($logFile);

$spec = [
    0 => ['pipe', 'r'],
    1 => ['file', $logFile, 'w'],
    2 => ['file', $logFile, 'w'],
];

$server = proc_open(
    [PHP_BINARY, '-S', '127.0.0.1:' . $port, '-t', BASE_PATH . '/public', BASE_PATH . '/public/index.php'],
    $spec,
    $pipes
);

if (!is_resource($server)) {
    echo "تعذّر تشغيل خادم الاختبار.\n";
    exit(1);
}

$ready = false;
for ($i = 0; $i < 50; $i++) {
    $probe = @fsockopen('127.0.0.1', $port, $errno, $errstr, 1);
    if ($probe !== false) {
        fclose($probe);
        $ready = true;
        break;
    }
    usleep(200000);
}

if (!$ready) {
    proc_terminate($server);
    proc_close($server);
    echo "الخادم لم يبدأ خلال 10 ثوانٍ. راجع: $logFile\n";
    exit(1);
}

echo "خادم الاختبار يعمل على 127.0.0.1:$port\n";
echo str_repeat('-', 60) . "\n";

$demoProduct = Database::first("SELECT id, slug, name FROM products WHERE is_active = 1 AND stock > 0 ORDER BY id LIMIT 1");
$firstCategory = Database::first('SELECT id, slug FROM categories ORDER BY id LIMIT 1');

// ضمان كلمة مرور حتمية لحساب العميلة التجريبي (نعيدها في النهاية أيضًا)
$demoCustomerId = (int) Database::scalar("SELECT id FROM users WHERE email = 'customer@example.com'");
if ($demoCustomerId > 0) {
    \App\Models\UserRepository::updatePassword($demoCustomerId, 'ChangeMe123!');
}

$testEmail = 'httptest-customer@example.test';
$testCatSlug = 'httptest-cat';
$testProdSlug = 'httptest-item';
$imgProdSlug = 'httptest-img';
$evilProdName = 'منتج برفع خبيث';

$orderId = 0;
$createdProducts = [];

try {
    // ==================================================================
    //  أ) الصفحات العامة
    // ==================================================================
    $guest = new Http($port);

    $home = $guest->get('/');
    check('الصفحة الرئيسية تُحمّل (200)', $home['status'] === 200, (string) $home['status']);
    check('الصفحة عربية RTL', str_contains($home['body'], 'lang="ar"') && str_contains($home['body'], 'dir="rtl"'));
    check('لديها عنوان ووصف meta', str_contains($home['body'], '<title>') && str_contains($home['body'], 'name="description"'));
    check('عنوان h1 واحد فقط في الرئيسية', substr_count($home['body'], '<h1') === 1, (string) substr_count($home['body'], '<h1'));
    check('رؤوس أمان HTTP موجودة', isset($home['headers']['x-content-type-options']) && $home['headers']['x-content-type-options'] === 'nosniff');
    check('إخفاء رأس X-Powered-By', !isset($home['headers']['x-powered-by']));

    // طلبات HEAD (يستخدمها فحّاصو الخوادم وبيانات المراقبة)
    $headHome = $guest->head('/');
    check('طلب HEAD للرئيسية يعيد 200', $headHome['status'] === 200, (string) $headHome['status']);
    $headList = $guest->head('/products');
    check('طلب HEAD لصفحة المنتجات يعيد 200', $headList['status'] === 200, (string) $headList['status']);
    $headMissing = $guest->head('/no-such-page-here');
    check('طلب HEAD لصفحة غير موجودة يعيد 404', $headMissing['status'] === 404, (string) $headMissing['status']);

    $list = $guest->get('/products');
    check('صفحة المنتجات تُحمّل (200)', $list['status'] === 200, (string) $list['status']);
    check('بطاقات المنتجات معروضة', str_contains($list['body'], 'card-product') || str_contains($list['body'], 'product-card'));

    $css = $guest->get('/assets/css/app.css');
    check('ملف CSS يُقدَّم (200)', $css['status'] === 200, (string) $css['status']);
    check('نوع المحتوى CSS صحيح', str_contains($css['headers']['content-type'] ?? '', 'text/css'));

    $catPage = $guest->get('/category/' . $firstCategory['slug']);
    check('صفحة قسم تُحمّل (200)', $catPage['status'] === 200, (string) $catPage['status']);

    $search = $guest->get('/products?q=' . rawurlencode($demoProduct['name']));
    check('البحث يعمل (200)', $search['status'] === 200, (string) $search['status']);

    $missing = $guest->get('/no-such-page-xyz');
    check('صفحة غير موجودة تعيد 404', $missing['status'] === 404, (string) $missing['status']);
    check('صفحة 404 تحمل noindex', str_contains($missing['body'], 'noindex'));

    // ==================================================================
    //  ب) التسجيل والدخول
    // ==================================================================
    $reg = new Http($port);
    $regPage = $reg->get('/register');
    check('صفحة التسجيل تُحمّل (200)', $regPage['status'] === 200, (string) $regPage['status']);

    $token = token_of($regPage['body']);
    check('رمز CSRF موجود في نموذج التسجيل', $token !== '');

    $registered = $reg->post('/register', [
        '_token' => $token,
        'name' => 'عميلة اختبار آلية',
        'email' => $testEmail,
        'phone' => '0910000000',
        'password' => 'TestPass1234',
        'password_confirmation' => 'TestPass1234',
    ], 'http://127.0.0.1:' . $port . '/register');
    check('التسجيل الناجح يوجّه إلى /account', $registered['status'] === 302 && str_contains($registered['headers']['location'] ?? '', '/account'), ($registered['headers']['location'] ?? ''));

    $createdUserId = (int) Database::scalar('SELECT id FROM users WHERE email = ?', [$testEmail]);
    check('المستخدم محفوظ وكلمة المرور مشفّرة', $createdUserId > 0 && str_starts_with((string) Database::scalar('SELECT password_hash FROM users WHERE id = ?', [$createdUserId]), '$'));

    // محاولة التسجيل مرة أخرى بجلسة جديدة (المسجلة حاليًا في جلسة أخرى)
    $dup = new Http($port);
    $dupPage = $dup->get('/register');
    $again = $dup->post('/register', [
        '_token' => token_of($dupPage['body']),
        'name' => 'عميلة اختبار آلية',
        'email' => $testEmail,
        'phone' => '0910000000',
        'password' => 'TestPass1234',
        'password_confirmation' => 'TestPass1234',
    ], 'http://127.0.0.1:' . $port . '/register');
    $againBody = ($again['status'] === 302 ? $dup->get('/register')['body'] : $again['body']);
    check('البريد المكرر يُرفض برسالة', str_contains($againBody, 'هذا البريد مسجّل مسبقًا'), 'status=' . $again['status']);

    // دخول خاطئ حتى الحظر
    $wrong = new Http($port);
    $loginPage = $wrong->get('/login');
    $loginToken = token_of($loginPage['body']);
    for ($i = 0; $i < 6; $i++) {
        $wrong->post('/login', [
            '_token' => $loginToken,
            'email' => $testEmail,
            'password' => 'WRONG-password',
        ], 'http://127.0.0.1:' . $port . '/login');
    }
    $throttledBody = $wrong->get('/login')['body'];
    check('حظر محاولات الدخول المتكررة', str_contains($throttledBody, 'عدد محاولات الدخول كبير'));

    // دخول ناجح للعميلة التجريبية
    $customer = new Http($port);
    $cLogin = $customer->get('/login');
    $cToken = token_of($cLogin['body']);
    $cOk = $customer->post('/login', [
        '_token' => $cToken,
        'email' => 'customer@example.com',
        'password' => 'ChangeMe123!',
    ], 'http://127.0.0.1:' . $port . '/login');
    check('دخول العميلة الناجح يوجّه إلى /account', $cOk['status'] === 302 && str_contains($cOk['headers']['location'] ?? '', '/account'));

    // ==================================================================
    //  ج) تجهيز بيانات الاختبار عبر اللوحة (المسؤولة)
    // ==================================================================
    $admin = new Http($port);
    $aLogin = $admin->get('/login');
    $aToken = token_of($aLogin['body']);
    $aOk = $admin->post('/login', [
        '_token' => $aToken,
        'email' => 'admin@example.com',
        'password' => 'ChangeMe123!',
    ], 'http://127.0.0.1:' . $port . '/login');
    check('دخول المسؤولة يوجّه إلى /admin', $aOk['status'] === 302 && str_contains($aOk['headers']['location'] ?? '', '/admin'));

    $catPage = $admin->get('/admin/categories');
    check('لوحة الأقسام متاحة للمسؤولة', $catPage['status'] === 200, (string) $catPage['status']);
    $aToken = token_of($catPage['body']);

    $admin->post('/admin/categories/store', [
        '_token' => $aToken,
        'name' => 'قسم الاختبار الآلي',
        'slug' => $testCatSlug,
        'description' => 'قسم مؤقت أنشأه سكربت الاختبار.',
        'emoji' => '🧪',
        'color' => '#aaccff',
    ], 'http://127.0.0.1:' . $port . '/admin/categories/create');
    $testCatId = (int) Database::scalar('SELECT id FROM categories WHERE slug = ?', [$testCatSlug]);
    check('إنشاء قسم جديد عبر اللوحة', $testCatId > 0);

    $admin->post('/admin/categories/' . $testCatId . '/update', [
        '_token' => $aToken,
        'name' => 'قسم الاختبار (محدث)',
        'slug' => $testCatSlug,
        'description' => 'تم التعديل.',
        'emoji' => '🧪',
        'color' => '#aaccff',
    ], 'http://127.0.0.1:' . $port . '/admin/categories/' . $testCatId . '/edit');
    check('تعديل القسم', (string) Database::scalar('SELECT name FROM categories WHERE id = ?', [$testCatId]) === 'قسم الاختبار (محدث)');

    // محاولة حذف قسم يحوي منتجات (قسم تجريبي حقيقي)
    $busyId = (int) Database::scalar('SELECT category_id FROM products GROUP BY category_id ORDER BY COUNT(*) DESC LIMIT 1');
    $admin->post('/admin/categories/' . $busyId . '/delete', ['_token' => $aToken], 'http://127.0.0.1:' . $port . '/admin/categories');
    check('حذف قسم يحوي منتجات مرفوض', (int) Database::scalar('SELECT COUNT(*) FROM categories WHERE id = ?', [$busyId]) === 1);

    // إنشاء منتج تجريبي للاختبار
    $prodPage = $admin->get('/admin/products/create');
    $pToken = token_of($prodPage['body']);
    $admin->post('/admin/products/store', [
        '_token' => $pToken,
        'name' => 'منتج الاختبار الآلي',
        'slug' => $testProdSlug,
        'category_id' => $testCatId,
        'price' => '12.5',
        'stock' => '5',
        'tagline' => 'منتج مؤقت للاختبار الآلي',
        'description' => 'وصف تجريبي من سكربت الاختبار الكامل.',
        'emoji' => '🧪',
        'color' => '#d1fae5',
        'is_active' => '1',
    ], 'http://127.0.0.1:' . $port . '/admin/products/create');
    $testProdId = (int) Database::scalar('SELECT id FROM products WHERE slug = ?', [$testProdSlug]);
    $createdProducts[] = $testProdId;
    check('إنشاء منتج عبر اللوحة', $testProdId > 0);

    $storefront = $guest->get('/products/' . $testProdSlug);
    check('المنتج الجديد ظاهر للزوار', $storefront['status'] === 200 && str_contains($storefront['body'], 'منتج الاختبار الآلي'));

    // مدة التحضير في صفحة المنتج
    $foodPage = $guest->get('/products/arabic-coffee-hail');
    check('منتج غذائي يعرض مدة التحضير (5 دقائق)', $foodPage['status'] === 200 && str_contains($foodPage['body'], 'التحضير: 5 دقيقة'));
    $dressPage = $guest->get('/products/embroidered-abaya');
    check('منتج ملابس لا يعرض مدة تحضير', $dressPage['status'] === 200 && !str_contains($dressPage['body'], 'التحضير:'));
    check('إعداد الإنتاج: الملابس صفحتها 0', (int) Database::scalar("SELECT prep_minutes FROM products WHERE slug = 'embroidered-abaya'") === 0);

    $dashboard = $admin->get('/admin');
    check('لوحة التحكم تعرض إجمالي المبيعات', str_contains($dashboard['body'], 'إجمالي المبيعات'));

    // ==================================================================
    //  د) السلة وإتمام الطلب (زائرة جديدة)
    // ==================================================================
    $shop = new Http($port);

    $cartEmpty = $shop->get('/cart');
    check('السلة تبدأ فارغة', str_contains($cartEmpty['body'], 'سلتك فارغة'));

    $noCsrf = $shop->post('/cart/add', ['product_id' => $testProdId, 'qty' => 1]);
    check('إرسال بدون رمز CSRF يُرفض (419)', $noCsrf['status'] === 419, (string) $noCsrf['status']);

    $productPage = $shop->get('/products/' . $testProdSlug);
    $sToken = token_of($productPage['body']);
    $added = $shop->post('/cart/add', [
        '_token' => $sToken,
        'product_id' => $testProdId,
        'qty' => 99,
    ], 'http://127.0.0.1:' . $port . '/products/' . $testProdSlug);
    check('الإضافة إلى السلة تُوجّه إلى السلة', $added['status'] === 302 && str_contains($added['headers']['location'] ?? '', '/cart'));

    $cartFull = $shop->get('/cart');
    check('الكمية مقيّدة بالمخزون (5)', str_contains($cartFull['body'], 'value="5"'), 'لم تُحدَّد الكمية');

    $shop->post('/cart/update', ['_token' => $sToken, 'qty' => [$testProdId => 2]], 'http://127.0.0.1:' . $port . '/cart');
    $cartTwo = $shop->get('/cart');
    check('تحديث الكمية إلى 2', str_contains($cartTwo['body'], 'value="2"'));
    check('الإجمالي الفرعي 25.000 د.ل', str_contains($cartTwo['body'], '25.000'));

    // رسوم التوصيل تظهر في صفحة الدفع حسب المدينة
    $checkoutPage = $shop->get('/checkout');
    check('صفحة الدفع تعرض مدن التوصيل ورسومها', str_contains($checkoutPage['body'], 'طرابلس — 5.000'));
    check('صفحة الدفع تشرح احتساب الرسوم', str_contains($checkoutPage['body'], 'يُحسب حسب المدينة'));

    // إتمام الطلب ببيانات ناقصة
    $shop->post('/checkout', ['_token' => $sToken, 'name' => 'زائرة اختبار', 'phone' => '0920000000', 'city' => 'طرابلس'], 'http://127.0.0.1:' . $port . '/checkout');
    $checkoutBack = $shop->get('/checkout');
    check('العنوان الناقص يمنع إتمام الطلب', str_contains($checkoutBack['body'], 'الحقل العنوان مطلوب'));

    // مدينة غير موجودة في الإعدادات
    $shop->post('/checkout', [
        '_token' => $sToken,
        'name' => 'زائرة اختبار',
        'phone' => '0920000000',
        'city' => 'مدينة وهمية جدا',
        'address' => 'شارع الاختبار رقم 7',
    ], 'http://127.0.0.1:' . $port . '/checkout');
    $badCityBack = $shop->get('/checkout');
    check('مدينة غير مدرجة تُرفض قبل إنشاء الطلب', str_contains($badCityBack['body'], 'غير مدرجة'));
    check('الطلب لم يُنشأ من مدينة غير مدرجة', (int) Database::scalar("SELECT COUNT(*) FROM orders WHERE customer_city = 'مدينة وهمية جدا'") === 0);

    // إتمام الطلب الصحيح
    $done = $shop->post('/checkout', [
        '_token' => $sToken,
        'name' => 'زائرة اختبار آلية',
        'phone' => '0920000000',
        'city' => 'طرابلس',
        'address' => 'شارع الاختبار رقم 7',
        'notes' => 'ملاحظة اختبار',
    ], 'http://127.0.0.1:' . $port . '/checkout');

    $location = $done['headers']['location'] ?? '';
    $orderId = preg_match('#/order/(\d+)#', $location, $m) === 1 ? (int) $m[1] : 0;
    check('إنشاء الطلب ينجح ويوجّه لصفحة التأكيد', $done['status'] === 302 && $orderId > 0, $location);

    $orderRow = $orderId > 0 ? Database::first('SELECT * FROM orders WHERE id = ?', [$orderId]) : null;
    check('الطلب محفوظ بالإجمالي 30.000 (25 + رسوم 5)', $orderRow !== null && abs((float) $orderRow['total'] - 30.0) < 0.001, (string) ($orderRow['total'] ?? 'null'));
    check('رسوم التوصيل محفوظة في الطلب (5.000)', $orderRow !== null && abs((float) ($orderRow['delivery_fee'] ?? -1) - 5.0) < 0.001, (string) ($orderRow['delivery_fee'] ?? 'null'));
    check('المخزون خُصم (5 ← 3)', (int) Database::scalar('SELECT stock FROM products WHERE id = ?', [$testProdId]) === 3);
    check('السلة أُفرغت بعد الطلب', str_contains($shop->get('/cart')['body'], 'سلتك فارغة'));

    $confirm = $shop->get('/order/' . $orderId);
    check('صفحة التأكيد تعرض الطلب (200)', $confirm['status'] === 200 && str_contains($confirm['body'], '25.000'));
    check('صفحة التأكيد تعرض الإجمالي مع الرسوم', str_contains($confirm['body'], '30.000'));
    check('صفحة التأكيد noindex', str_contains($confirm['body'], 'noindex'));

    $otherView = (new Http($port))->get('/order/' . $orderId);
    check('جلسة أخرى لا ترى الطلب (404)', $otherView['status'] === 404, (string) $otherView['status']);

    // حظر الإرسال المتكرر للطلبات
    $flood = new Http($port);
    $floodToken = token_of($flood->get('/products/' . $testProdSlug)['body']);
    $flood->post('/cart/add', ['_token' => $floodToken, 'product_id' => $testProdId, 'qty' => 1], 'http://127.0.0.1:' . $port . '/products/' . $testProdSlug);
    $floodToken = token_of($flood->get('/checkout')['body']);
    for ($i = 0; $i < 6; $i++) {
        $flood->post('/checkout', [
            '_token' => $floodToken,
            'name' => 'زائرة',
            'phone' => '0920000000',
            'city' => 'طرابلس',
        ], 'http://127.0.0.1:' . $port . '/checkout');
    }
    $floodCart = $flood->get('/cart');
    check('مقيّد معدل الطلبات يمنع الإغراق', str_contains($floodCart['body'], 'طلبات كثيرة في وقت قصير'));

    // ==================================================================
    //  هـ) مصفوفة الصلاحيات
    // ==================================================================
    check('زائرة: /admin يعيد توجيهًا للدخول', (new Http($port))->get('/admin')['status'] === 302);
    check('عميلة: /admin ممنوع (403)', $customer->get('/admin')['status'] === 403);
    check('عميلة: /admin/products ممنوع (403)', $customer->get('/admin/products')['status'] === 403);
    check('عميلة: /account متاح (200)', $customer->get('/account')['status'] === 200);

    $seller = new Http($port);
    $sLogin = $seller->get('/login');
    $seller->post('/login', [
        '_token' => token_of($sLogin['body']),
        'email' => 'seller@example.com',
        'password' => 'ChangeMe123!',
    ], 'http://127.0.0.1:' . $port . '/login');
    check('بائعة: /admin متاح (200)', $seller->get('/admin')['status'] === 200);
    check('بائعة: /admin/orders متاح (200)', $seller->get('/admin/orders')['status'] === 200);
    check('بائعة: /admin/users ممنوع (403)', $seller->get('/admin/users')['status'] === 403);
    check('بائعة: /admin/categories ممنوع (403)', $seller->get('/admin/categories')['status'] === 403);
    check('بائعة: /admin/settings ممنوع (403)', $seller->get('/admin/settings')['status'] === 403);
    check('بائعة: POST /admin/categories/store ممنوع (403)', $seller->post('/admin/categories/store', ['_token' => $aToken, 'name' => 'x', 'description' => 'x'])['status'] === 403);

    check('مسؤولة: /admin/users متاح (200)', $admin->get('/admin/users')['status'] === 200);

    // ==================================================================
    //  ز2) إعدادات المتجر (رسوم التوصيل حسب المدينة)
    // ==================================================================
    $origCityList = \App\Models\SettingRepository::deliveryCities();
    $origNote = (string) Database::scalar("SELECT value FROM settings WHERE key = 'delivery_note'");
    $origPhone = (string) Database::scalar("SELECT value FROM settings WHERE key = 'store_phone'");

    $settingsPage = $admin->get('/admin/settings');
    check('مسؤولة: /admin/settings متاح (200)', $settingsPage['status'] === 200 && str_contains($settingsPage['body'], 'مدن التوصيل'));
    check('عميلة: /admin/settings ممنوع (403)', $customer->get('/admin/settings')['status'] === 403);

    $settingsToken = token_of($settingsPage['body']);
    $admin->post('/admin/settings/update', [
        '_token' => $settingsToken,
        'city_name' => ['طرابلس', 'بنغازي'],
        'city_fee' => ['6.500', '8.000'],
        'delivery_note' => 'ملاحظة اختبار التوصيل',
        'store_phone' => '0911111111',
    ], 'http://127.0.0.1:' . $port . '/admin/settings');

    check('حفظ إعدادات التوصيل من اللوحة', (string) Database::scalar("SELECT value FROM settings WHERE key = 'delivery_note'") === 'ملاحظة اختبار التوصيل'
        && abs((float) \App\Models\SettingRepository::deliveryFeeFor('طرابلس') - 6.5) < 0.001);

    // رسوم غير رقمية تُرفض ولا تُمسح الإعدادات
    $admin->post('/admin/settings/update', [
        '_token' => $settingsToken,
        'city_name' => ['طرابلس'],
        'city_fee' => ['ليس_رقم'],
        'delivery_note' => 'يجب ألا تُحفظ',
        'store_phone' => '0911111111',
    ], 'http://127.0.0.1:' . $port . '/admin/settings');
    check('رسوم غير رقمية تُرفض وتُبقي الإعداد القديم', abs((float) \App\Models\SettingRepository::deliveryFeeFor('طرابلس') - 6.5) < 0.001
        && (string) Database::scalar("SELECT value FROM settings WHERE key = 'delivery_note'") === 'ملاحظة اختبار التوصيل');

    // بدون رمز CSRF يُرفض الحفظ
    $admin->post('/admin/settings/update', [
        'city_name' => ['طرابلس'],
        'city_fee' => ['1'],
        'delivery_note' => 'بدون رمز',
        'store_phone' => '0911111111',
    ], 'http://127.0.0.1:' . $port . '/admin/settings');
    check('POST بدون CSRF للإعدادات يُرفض (419)', abs((float) \App\Models\SettingRepository::deliveryFeeFor('طرابلس') - 6.5) < 0.001);

    // استعادة القيم الأصلية قبل إغلاق الاختبار
    $admin->post('/admin/settings/update', [
        '_token' => $settingsToken,
        'city_name' => array_column($origCityList, 'name'),
        'city_fee' => array_map(static fn (array $c): string => number_format($c['fee'], 3, '.', ''), $origCityList),
        'delivery_note' => $origNote,
        'store_phone' => $origPhone,
    ], 'http://127.0.0.1:' . $port . '/admin/settings');
    check('استعادة إعدادات التوصيل الأصلية', count(\App\Models\SettingRepository::deliveryCities()) === count($origCityList)
        && (string) Database::scalar("SELECT value FROM settings WHERE key = 'delivery_note'") === $origNote
        && abs((float) \App\Models\SettingRepository::deliveryFeeFor('طرابلس') - 5.0) < 0.001);

    // ==================================================================
    //  و) إدارة الطلبات في اللوحة
    // ==================================================================
    $ordersPage = $admin->get('/admin/orders');
    check('قائمة الطلبات تعرض الطلب الجديد', str_contains($ordersPage['body'], '#' . $orderId));

    $details = $admin->get('/admin/orders/' . $orderId);
    check('تفاصيل الطلب تعرض البنود وبيانات العميلة', $details['status'] === 200
        && str_contains($details['body'], 'زائرة اختبار آلية')
        && str_contains($details['body'], 'منتج الاختبار الآلي'));
    check('تفاصيل الطلب تعرض رسوم التوصيل (طرابلس)', str_contains($details['body'], 'التوصيل (طرابلس)') && str_contains($details['body'], '5.000'));

    $statusToken = token_of($ordersPage['body']);
    $admin->post('/admin/orders/' . $orderId . '/status', ['_token' => $statusToken, 'status' => 'confirmed'], 'http://127.0.0.1:' . $port . '/admin/orders');
    check('تغيير حالة الطلب', (string) Database::scalar('SELECT status FROM orders WHERE id = ?', [$orderId]) === 'confirmed');

    $admin->post('/admin/orders/' . $orderId . '/status', ['_token' => $statusToken, 'status' => 'hack-status'], 'http://127.0.0.1:' . $port . '/admin/orders');
    check('حالة غير مسموحة تُرفض', (string) Database::scalar('SELECT status FROM orders WHERE id = ?', [$orderId]) === 'confirmed');

    // ==================================================================
    //  ز) الأمان
    // ==================================================================
    // XSS في البحث
    $xss = $guest->get('/products?q=' . rawurlencode('<script>alert(1)</script>'));
    check('XSS في البحث يُهرَّب', $xss['status'] === 200 && !str_contains($xss['body'], '<script>alert(1)</script>'));

    // حقن SQL في البحث
    $sqli = $guest->get('/products?q=' . rawurlencode("' OR '1'='1"));
    check('حقن SQL لا يكسر الصفحة', $sqli['status'] === 200 && !str_contains($sqli['body'], 'SQLSTATE') && !str_contains($sqli['body'], 'syntax error'));
    check('قاعدة البيانات سليمة بعد الحقن', (int) Database::scalar('SELECT COUNT(*) FROM products') >= $productCount);

    // تجاوز المسارات لقراءة ملفات
    $traversal = $guest->get('/uploads/' . rawurlencode('../.env'));
    check('محاولة قراءة .env عبر الرفع تُرفض', $traversal['status'] === 404, (string) $traversal['status']);

    // رفع ملف PHP مزيّف
    $beforeEvil = (int) Database::scalar('SELECT COUNT(*) FROM products WHERE name = ?', [$evilProdName]);
    $admin->postMultipart('/admin/products/store', [
        '_token' => $pToken,
        'name' => $evilProdName,
        'slug' => 'httptest-evil',
        'category_id' => $testCatId,
        'price' => '1',
        'stock' => '1',
        'tagline' => 'ملف خبيث',
        'description' => 'محاولة رفع ملف PHP مزيّف باسم صورة.',
    ], [[
        'name' => 'image',
        'filename' => 'shell.php',
        'type' => 'image/png',
        'content' => "<?php echo 'pwned'; ?>",
    ]], 'http://127.0.0.1:' . $port . '/admin/products/create');
    $afterEvil = (int) Database::scalar('SELECT COUNT(*) FROM products WHERE name = ?', [$evilProdName]);
    $evilFiles = glob(BASE_PATH . '/storage/uploads/*.php') ?: [];
    check('رفع PHP مزيّف مرفوض ولا يُنتج منتجًا', $afterEvil === $beforeEvil && $evilFiles === []);

    // رفع صورة PNG حقيقية
    $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');
    $admin->postMultipart('/admin/products/store', [
        '_token' => $pToken,
        'name' => 'منتج بصورة حقيقية',
        'slug' => $imgProdSlug,
        'category_id' => $testCatId,
        'price' => '5',
        'stock' => '1',
        'tagline' => 'منتج بصورة PNG',
        'description' => 'منتج اختباري بصورة مرفوعة بشكل صحيح.',
        'is_active' => '1',
    ], [[
        'name' => 'image',
        'filename' => 'pixel.png',
        'type' => 'image/png',
        'content' => $png,
    ]], 'http://127.0.0.1:' . $port . '/admin/products/create');

    $imgProdId = (int) Database::scalar('SELECT id FROM products WHERE slug = ?', [$imgProdSlug]);
    $createdProducts[] = $imgProdId;
    $imageName = $imgProdId > 0 ? (string) Database::scalar('SELECT image FROM products WHERE id = ?', [$imgProdId]) : '';
    check('رفع PNG صحيح يُحفظ خارج العام', $imgProdId > 0 && $imageName !== '' && is_file(BASE_PATH . '/storage/uploads/' . $imageName));

    if ($imageName !== '') {
        $served = $guest->get('/uploads/' . $imageName);
        check('الصورة تُقدَّم عبر /uploads (200 + image/png)', $served['status'] === 200 && str_contains($served['headers']['content-type'] ?? '', 'image/png'), (string) $served['status']);
    } else {
        check('الصورة تُقدَّم عبر /uploads (200 + image/png)', false, 'لا توجد صورة مرفوعة');
    }

    // تغيير كلمة المرور
    $pwPage = $customer->get('/account/password');
    $pwToken = token_of($pwPage['body']);
    $customer->post('/account/password', [
        '_token' => $pwToken,
        'current_password' => 'WRONG',
        'password' => 'Changed12345',
        'password_confirmation' => 'Changed12345',
    ], 'http://127.0.0.1:' . $port . '/account/password');
    check('كلمة مرور حالية خاطئة تُرفض', str_contains($customer->get('/account/password')['body'], 'كلمة المرور الحالية غير صحيحة'));

    $customer->post('/account/password', [
        '_token' => $pwToken,
        'current_password' => 'ChangeMe123!',
        'password' => 'Changed12345',
        'password_confirmation' => 'Changed12345',
    ], 'http://127.0.0.1:' . $port . '/account/password');
    check('تغيير كلمة المرور يسجّل الخروج', $customer->get('/account')['status'] === 302);
    check('كلمة المرور الجديدة تعمل في الدخول', (function () use ($port, $testEmail): bool {
        $c = new Http($port);
        $page = $c->get('/login');
        $r = $c->post('/login', [
            '_token' => token_of($page['body']),
            'email' => 'customer@example.com',
            'password' => 'Changed12345',
        ], 'http://127.0.0.1:' . $port . '/login');

        return $r['status'] === 302 && str_contains($r['headers']['location'] ?? '', '/account');
    })());
    // ===== المرحلة السابعة: SEO والمراقبة =====
    $robots = $guest->get('/robots.txt');
    check('robots.txt يُقدَّم كنص (200)', $robots['status'] === 200 && str_contains($robots['headers']['content-type'] ?? '', 'text/plain'), (string) $robots['status']);
    check('robots.txt يحجب صفحات الإدارة والحساب', str_contains($robots['body'], 'Disallow: /admin') && str_contains($robots['body'], 'Disallow: /account'));
    check('robots.txt يعلن خريطة الموقع', str_contains($robots['body'], 'Sitemap: '));

    $sitemap = $guest->get('/sitemap.xml');
    check('sitemap.xml يُقدَّم كـ XML (200)', $sitemap['status'] === 200 && str_contains($sitemap['body'], '<urlset'), (string) $sitemap['status']);
    check('sitemap.xml يحوي رابط المنتج التجريبي', str_contains($sitemap['body'], '/products/' . $testProdSlug));
    check('sitemap.xml يحوي روابط الأقسام', str_contains($sitemap['body'], '/category/'));
    check('sitemap.xml لا يحوي صفحات محمية', !str_contains($sitemap['body'], '/admin'));

    $health = $guest->get('/healthz');
    check('healthz يعيد حالة سليمة', $health['status'] === 200 && str_contains($health['body'], '"status":"ok"'), $health['body']);

    check('أيقونة favicon مربوطة في الترويسة', str_contains($home['body'], 'favicon.svg'));

    // ملفات التثبيت (PWA) — روابط تنزيل المنصة على أندرويد وويندوز
    $manifestReq = $guest->get('/manifest.webmanifest');
    check('manifest.webmanifest يُقدَّم (200 + اسم عربي)', $manifestReq['status'] === 200
        && str_contains($manifestReq['body'], 'standalone')
        && str_contains($manifestReq['body'], 'المتجر الليبي'));
    check('الترويسة تعلق manifest للتطبيق', str_contains($home['body'], 'rel="manifest"'));
    $swReq = $guest->get('/sw.js');
    check('sw.js (عامل الخدمة) يُقدَّم (200)', $swReq['status'] === 200 && str_contains($swReq['body'], "addEventListener('fetch'"));
    check('og:url موجود في الترويسة', str_contains($home['body'], 'property="og:url"'));
    check('رابط CSS يحمل رقم نسخة (?v=) لتفادي الكاش', str_contains($home['body'], 'app.css?v='));
    check('بيانات JSON-LD للمنتج موجودة', str_contains($storefront['body'], 'application/ld+json') && str_contains($storefront['body'], '"priceCurrency"'));

    // سجل الأخطاء: لا يجوز أن يسجّل التطبيق أي خطأ أثناء كل السيناريوهات
    $appLog = BASE_PATH . '/storage/logs/app.log';
    $logTail = '';
    if (is_file($appLog)) {
        $handle = fopen($appLog, 'rb');
        if ($handle !== false) {
            fseek($handle, $logStartSize);
            $logTail = (string) stream_get_contents($handle);
            fclose($handle);
        }
    }
    $badLog = preg_match('/(PHP (Warning|Fatal error|Deprecated|Notice)|Uncaught)/', $logTail) === 1;
    check('لا أخطاء PHP مسجّلة في سجل التطبيق', !$badLog, trim(preg_replace('/\s+/', ' ', substr($logTail, 0, 300))));
} catch (Throwable $e) {
    $GLOBALS['__failed']++;
    $GLOBALS['__fails'][] = 'استثناء غير متوقع';
    echo '[FAIL] استثناء غير متوقع — ' . $e->getMessage() . "\n";
    echo $e->getFile() . ':' . $e->getLine() . "\n";
} finally {
    // ==================================================================
    //  تنظيف كل بيانات الاختبار
    // ==================================================================
    if ($orderId > 0) {
        Database::delete('order_items', 'order_id = ?', [$orderId]);
        Database::delete('orders', 'id = ?', [$orderId]);
    }
    Database::delete('orders', "customer_name LIKE '%اختبار آلية%' OR customer_name LIKE '%زائرة اختبار%'");
    Database::run('DELETE FROM order_items WHERE order_id NOT IN (SELECT id FROM orders)');

    foreach ($createdProducts as $pid) {
        if ($pid > 0) {
            Database::delete('products', 'id = ?', [$pid]);
        }
    }
    Database::run("DELETE FROM products WHERE slug IN ('httptest-item', 'httptest-img', 'httptest-evil')");
    Database::delete('categories', 'slug = ?', [$testCatSlug]);
    Database::delete('users', 'email = ?', [$testEmail]);
    Database::run('DELETE FROM order_items WHERE order_id NOT IN (SELECT id FROM orders)');

    // إعادة كلمة مرور العميلة التريبية كما كانت
    if (!empty($demoCustomerId)) {
        \App\Models\UserRepository::updatePassword((int) $demoCustomerId, 'ChangeMe123!');
    }

    foreach (glob(BASE_PATH . '/storage/uploads/*') ?: [] as $file) {
        if (is_file($file) && filemtime($file) >= $startedAt) {
            @unlink($file);
        }
    }

    // إيقاف الخادم المؤقت
    if (isset($server) && is_resource($server)) {
        @fclose($pipes[0]);
        proc_terminate($server);
        proc_close($server);
    }
}

// ======================================================================
//  النتيجة
// ======================================================================

echo str_repeat('-', 60) . "\n";
echo 'النتيجة: ' . $GLOBALS['__passed'] . ' ناجح، ' . $GLOBALS['__failed'] . " فاشل\n";

if ($GLOBALS['__failed'] > 0) {
    echo "فشل في:\n";
    foreach ($GLOBALS['__fails'] as $label) {
        echo " - $label\n";
    }
    exit(1);
}

echo "كل اختبارات HTTP ناجحة ✅\n";
exit(0);
