<?php

declare(strict_types=1);

use App\Core\Env;
use App\Core\Lang;
use App\Core\Session;

/**
 * دوال مساعدة صغيرة تُستخدم في كل مكان بالمشروع.
 */

/** تحويل أي نص إلى آمن قبل عرضه في الصفحة (منع حقن HTML). */
function e(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** قراءة قيمة من ملف .env */
function env(string $key, string $default = ''): string
{
    return Env::get($key, $default);
}

/** رابط داخلي داخل الموقع: url('products') => /products */
function url(string $path = ''): string
{
    return '/' . ltrim($path, '/');
}

/** رابط ملف ثابت مع رقم نسخة لتفادي الكاش: asset('css/app.css') => /assets/css/app.css?v=1730000000 */
function asset(string $path): string
{
    $relative = ltrim($path, '/');
    $file = BASE_PATH . '/public/assets/' . $relative;
    $version = is_file($file) ? (string) filemtime($file) : '';

    return '/assets/' . $relative . ($version !== '' ? '?v=' . $version : '');
}

/** الرابط المطلق للصفحة (يُستخدم في sitemap و open graph) */
function absolute_url(string $path = ''): string
{
    $base = rtrim((string) env('APP_URL', ''), '/');

    return $base . url($path);
}

/** ترجمة نص حسب اللغة الحالية: __('nav.home') */
function __(string $key, array $replace = []): string
{
    return Lang::get($key, $replace);
}

/** تنسيق السعر بالدينار الليبي: 12.500 د.ل */
function format_price(int|float $amount): string
{
    $decimals = Env::int('APP_CURRENCY_DECIMALS', 3);
    $symbol = Env::get('APP_CURRENCY_SYMBOL', 'د.ل');

    return number_format((float) $amount, $decimals, '.', ',') . ' ' . $symbol;
}

/** تحويل نص (عربي أو إنجليزي) إلى معرّف صالح للروابط: "كتاب جميل" => "كتاب-جميل" */
function slugify(string $text): string
{
    $text = trim(mb_strtolower($text));
    $text = preg_replace('/\s+/u', '-', $text) ?? $text;
    // نحتفظ بالحروف العربية والأرقام، ونستبدل الباقي بشرطات
    $text = preg_replace('/[^\p{L}\p{N}\-_]+/u', '-', $text) ?? $text;
    $text = preg_replace('/-+/', '-', $text) ?? $text;

    return trim($text, '-');
}

/** التحويل إلى صفحة أخرى ثم إيقاف التنفيذ */
function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

/** حفظ رسالة قصيرة تظهر مرة واحدة في الصفحة التالية */
function flash(string $key, string $message, string $type = 'success'): void
{
    Session::flash($key, ['message' => $message, 'type' => $type]);
}

/** جلب رسالة الاختبارات والتحقق من النماذج */
function flash_messages(): array
{
    $out = [];
    foreach (Session::flashData() as $key => $value) {
        if (is_array($value) && isset($value['message'])) {
            $out[$key] = $value;
        }
    }

    return $out;
}

/** عدد قطع سلة المشتريات */
function cart_count(): int
{
    return \App\Core\Cart::count();
}