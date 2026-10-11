<?php

declare(strict_types=1);

namespace App\Core;

/**
 * عرض الصفحات: ملف للقالب وملف للمحتوى.
 */
final class View
{
    /**
     * @param array<string,mixed> $data
     * @param string|null $layout ملف القالب، أو null لعرض المحتوى وحده
     */
    public static function render(string $view, array $data = [], ?string $layout = 'layouts/main'): void
    {
        $content = self::partial($view, $data);

        if ($layout === null) {
            echo $content;
            return;
        }

        $page = array_merge([
            'title' => Env::get('APP_NAME', 'المتجر الليبي'),
            'description' => '',
            'canonical' => '/',
            'body_class' => '',
        ], $data['page'] ?? []);

        extract($data, EXTR_SKIP);

        $file = BASE_PATH . '/views/' . $layout . '.php';
        if (!is_file($file)) {
            throw new \RuntimeException('القالب غير موجود: ' . $layout);
        }

        include $file;
    }

    /** @param array<string,mixed> $data */
    public static function partial(string $view, array $data = []): string
    {
        $file = BASE_PATH . '/views/' . $view . '.php';
        if (!is_file($file)) {
            throw new \RuntimeException('الصفحة غير موجودة: ' . $view);
        }

        extract($data, EXTR_SKIP);

        ob_start();
        include $file;

        return (string) ob_get_clean();
    }
}