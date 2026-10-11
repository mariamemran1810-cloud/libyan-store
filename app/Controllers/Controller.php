<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;

/**
 * الفئة الأساسية لكل وحدات التحكم (Controllers).
 */
abstract class Controller
{
    /** عرض صفحة داخل القالب العام */
    protected function view(string $view, array $data = [], ?string $layout = 'layouts/main'): void
    {
        View::render($view, $data, $layout);
    }

    /** رد بصيغة JSON (تُستخدم لاحقًا لواجهات AJAX) */
    protected function json(array $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);

        exit;
    }

    /** العودة إلى الصفحة السابقة بأمان */
    protected function back(string $fallback = '/'): never
    {
        $referer = (string) ($_SERVER['HTTP_REFERER'] ?? '');
        $path = $referer !== '' ? (string) parse_url($referer, PHP_URL_PATH) : $fallback;

        header('Location: ' . ($path ?: $fallback));
        exit;
    }
}