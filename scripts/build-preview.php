<?php

declare(strict_types=1);

/**
 * بناء معاينة ثابتة للمتجر على GitHub Pages.
 *
 * يجلب الصفحة الرئيسية من خادم محلي شغّال وينتج منها ملفًا ثابتًا
 * في docs/demo/ يمكن فتحه وتثبيته (PWA) دون خادم PHP.
 *
 * الاستخدام (من جذر المشروع):
 *     php -S 127.0.0.1:8000 -t public public/index.php &
 *     php scripts/build-preview.php
 */

$base = getenv('PREVIEW_BASE') ?: 'http://127.0.0.1:8000';
$demoUrl = 'https://mariamemran1810-cloud.github.io/libyan-store/demo/';
$outDir = dirname(__DIR__) . '/docs/demo';

echo "=> جلب الصفحة الرئيسية من {$base}\n";
$html = @file_get_contents($base . '/');
if ($html === false) {
    fwrite(STDERR, "تعذّر الاتصال بالخادم المحلي. شغّليه أولًا:\n  php -S 127.0.0.1:8000 -t public public/index.php\n");
    exit(1);
}

if (!is_dir($outDir)) {
    mkdir($outDir, 0775, true);
}

// 1) نسخ ملفات الثابتة محليًا
foreach (['assets/css/app.css' => 'app.css', 'favicon.svg' => 'favicon.svg'] as $from => $to) {
    $data = @file_get_contents(dirname(__DIR__) . '/public/' . $from);
    if ($data === false) {
        fwrite(STDERR, "ملف مفقود: public/{$from}\n");
        exit(1);
    }
    file_put_contents($outDir . '/' . $to, $data);
}

// 2) إعادة كتابة الروابط لتعمل من المجلد الثابت
$html = preg_replace('#href="/assets/css/app\.css[^"]*"#', 'href="app.css"', $html);
$html = preg_replace('#\s+src="/assets/js/app\.js[^"]*"#', '', $html);        // لا نحتاج جافاسكربت التطبيق هنا
$html = preg_replace('#href="/manifest\.webmanifest"#', 'href="manifest.json"', $html);
$html = preg_replace('#href="/manifest\.json"#', 'href="manifest.json"', $html);
$html = preg_replace('#href="/favicon\.svg"#', 'href="favicon.svg"', $html);
$html = preg_replace('#href="/"#', 'href="../"', $html);                      // العلامة ← صفحة العرض
$html = preg_replace('#(action|href)="/[^"]*"#', '$1="#"', $html);            // بقية الصفحات ← غير متاحة في المعاينة
$html = str_replace(['http://localhost:8000', 'https://libyan-store.onrender.com'], $demoUrl, $html);

// 3) شريط تنبيه يوضح أن هذه معاينة ثابتة
$banner = <<<'HTML'
<div class="container" style="margin-top:14px;background:#0f766e;color:#fff;border-radius:12px;padding:12px 16px;font-size:14px;text-align:center">
    👀 <b>معاينة ثابتة</b> للمنصة — السلة والحسابات والطلبات تعمل في النسخة الكاملة على الخادم.
</div>
HTML;
$html = preg_replace('#<body([^>]*)>#', '<body$1>' . $banner, $html, 1);

// 4) تسجيل عامل الخدمة (لتثبيت التطبيق على أندرويد وويندوز)
$inline = "\n<script>if('serviceWorker' in navigator){navigator.serviceWorker.register('./sw.js');}</script>\n</body>";
$html = str_replace('</body>', $inline, $html);

file_put_contents($outDir . '/index.html', $html);

// 5) ملف تعريف التطبيق (PWA)
file_put_contents($outDir . '/manifest.json', json_encode([
    'name' => 'المتجر الليبي — معاينة',
    'short_name' => 'المتجر الليبي',
    'description' => 'معاينة المتجر الليبي: أطعمة، مقاهي، ملابس.',
    'lang' => 'ar',
    'dir' => 'rtl',
    'start_url' => './',
    'scope' => './',
    'display' => 'standalone',
    'background_color' => '#f6f7f9',
    'theme_color' => '#0f766e',
    'icons' => [
        ['src' => 'favicon.svg', 'sizes' => 'any', 'type' => 'image/svg+xml', 'purpose' => 'any'],
        ['src' => 'favicon.svg', 'sizes' => 'any', 'type' => 'image/svg+xml', 'purpose' => 'maskable'],
    ],
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

// 6) عامل خدمة مصغّر للمعاينة
file_put_contents($outDir . '/sw.js', <<<'JS'
/* عامل خدمة المعاينة الثابتة */
const CACHE = 'libyan-store-demo-v1';
const FILES = ['./', './index.html', './app.css', './favicon.svg', './manifest.json'];

self.addEventListener('install', (e) => {
    e.waitUntil(caches.open(CACHE).then((c) => c.addAll(FILES)).then(() => self.skipWaiting()));
});
self.addEventListener('activate', (e) => {
    e.waitUntil(caches.keys().then((k) => Promise.all(k.filter((x) => x !== CACHE).map((x) => caches.delete(x)))).then(() => self.clients.claim()));
});
self.addEventListener('fetch', (e) => {
    if (e.request.method !== 'GET' || new URL(e.request.url).origin !== self.location.origin) return;
    e.respondWith(fetch(e.request).then((r) => {
        const copy = r.clone();
        caches.open(CACHE).then((c) => c.put(e.request, copy));
        return r;
    }).catch(() => caches.match(e.request)));
});
JS . "\n");

echo "=> تم توليد المعاينة في docs/demo/ (" . strlen($html) . " بايت HTML)\n";
echo "   الرابط بعد النشر: {$demoUrl}\n";
