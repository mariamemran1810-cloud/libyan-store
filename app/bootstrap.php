<?php

declare(strict_types=1);

use App\Core\Env;
use App\Core\Errors;
use App\Core\Lang;
use App\Core\Session;

// 1) أدوات أساسية
require __DIR__ . '/Core/Env.php';
require __DIR__ . '/Core/helpers.php';
require __DIR__ . '/Core/Errors.php';

// 2) قراءة ملف الإعدادات .env (لا توجد كلمات مرور في الكود أبدًا)
Env::load(dirname(__DIR__) . '/.env');

// 3) إعدادات عامة
mb_internal_encoding('UTF-8');
date_default_timezone_set(Env::get('APP_TIMEZONE', 'Africa/Tripoli'));

if (Env::bool('APP_DEBUG')) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(E_ALL);
}

// 4) رؤوس أمان HTTP (تُرسل قبل أي مخرجات)
if (PHP_SAPI !== 'cli' && !headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    header_remove('X-Powered-By');
}

// 5) تحميل الكلاسات تلقائيًا من مجلد app/ وفق تسمية App\...\Class
spl_autoload_register(static function (string $class): void {
    if (!str_starts_with($class, 'App\\')) {
        return;
    }
    $relative = substr($class, strlen('App\\'));
    $file = BASE_PATH . '/app/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

// 6) معالجة الأخطاء بالعربية + تسجيلها في storage/logs/app.log
Errors::register();

// 7) الجلسة ثم اللغة
Session::start();
Lang::boot();