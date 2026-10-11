<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;

/**
 * ملفات تخدم محركات البحث والمراقبة:
 * - /sitemap.xml : خريطة الموقع (تُبنى من قاعدة البيانات)
 * - /robots.txt  : تعليمات الزحف + رابط الخريطة
 * - /healthz     : فحص صحة الخادم (لا يعرض أي بيانات حساسة)
 */
final class MetaController extends Controller
{
    public function sitemap(): void
    {
        $base = rtrim((string) env('APP_URL', ''), '/');

        $rows = [];

        $rows[] = ['loc' => $base . '/', 'priority' => '1.0', 'changefreq' => 'daily'];

        $rows[] = ['loc' => $base . '/products', 'priority' => '0.9', 'changefreq' => 'daily'];
        $rows[] = ['loc' => $base . '/categories', 'priority' => '0.7', 'changefreq' => 'weekly'];

        foreach (Database::all('SELECT slug FROM categories ORDER BY position ASC') as $category) {
            $rows[] = [
                'loc' => $base . '/category/' . $category['slug'],
                'priority' => '0.7',
                'changefreq' => 'weekly',
            ];
        }

        foreach (Database::all(
            'SELECT slug, created_at FROM products WHERE is_active = 1 ORDER BY id ASC'
        ) as $product) {
            $rows[] = [
                'loc' => $base . '/products/' . $product['slug'],
                'priority' => '0.6',
                'changefreq' => 'monthly',
                'lastmod' => substr((string) $product['created_at'], 0, 10),
            ];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($rows as $row) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . e((string) $row['loc']) . '</loc>' . "\n";
            if (isset($row['lastmod'])) {
                $xml .= '    <lastmod>' . e((string) $row['lastmod']) . '</lastmod>' . "\n";
            }
            $xml .= '    <changefreq>' . e((string) $row['changefreq']) . '</changefreq>' . "\n";
            $xml .= '    <priority>' . e((string) $row['priority']) . '</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }

        $xml .= '</urlset>';

        $this->raw($xml, 'application/xml; charset=utf-8');
    }

    public function robots(): void
    {
        $base = rtrim((string) env('APP_URL', ''), '/');

        $lines = [
            'User-agent: *',
            'Allow: /$',
            'Disallow: /admin',
            'Disallow: /account',
            'Disallow: /cart',
            'Disallow: /checkout',
            'Disallow: /order',
            'Disallow: /search',
            'Disallow: /login',
            'Disallow: /register',
            'Allow: /',
            '',
            '# خريطة الموقع تساعد محركات البحث على اكتشاف الصفحات',
            'Sitemap: ' . $base . '/sitemap.xml',
        ];

        $this->raw(implode("\n", $lines) . "\n", 'text/plain; charset=utf-8');
    }

    /** فحص صحة للمراقبة/الاستضافة: هل الموقع يعمل وقاعدة البيانات متصلة؟ */
    public function healthz(): void
    {
        $dbOk = false;
        try {
            $dbOk = (int) Database::scalar('SELECT 1') === 1;
        } catch (\Throwable) {
            $dbOk = false;
        }

        $this->json([
            'status' => $dbOk ? 'ok' : 'degraded',
            'database' => $dbOk ? 'ok' : 'down',
            'time' => date('c'),
            'version' => '1.0.0',
        ], $dbOk ? 200 : 503);
    }

    /** إرسال رد خام (XML/نص) دون قوالب */
    private function raw(string $content, string $type): never
    {
        header('Content-Type: ' . $type);
        header('Cache-Control: public, max-age=3600');
        echo $content;

        exit;
    }
}
