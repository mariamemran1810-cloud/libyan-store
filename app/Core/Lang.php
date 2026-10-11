<?php

declare(strict_types=1);

namespace App\Core;

/**
 * نظام اللغات: العربية هي الافتراضية، والإنجليزية جاهزة للإضافة مستقبلًا
 * بمجرد إنشاء ملف lang/en.php واملئه.
 */
final class Lang
{
    public const AVAILABLE = ['ar', 'en'];

    /** @var array<string,mixed> نصوص اللغة الحالية */
    private static array $lines = [];

    /** @var array<string,mixed> نصوص العربية (احتياطي لأي مفتاح مفقود) */
    private static array $ar = [];

    private static string $locale = 'ar';

    public static function boot(): void
    {
        $locale = (string) (Session::get('locale') ?? Env::get('APP_LOCALE', 'ar'));
        self::load($locale);
    }

    private static function load(string $locale): void
    {
        if (!in_array($locale, self::AVAILABLE, true)) {
            $locale = 'ar';
        }

        self::$locale = $locale;

        $file = BASE_PATH . '/lang/' . $locale . '.php';
        self::$lines = is_file($file) ? (require $file) : [];

        if ($locale === 'ar') {
            self::$ar = self::$lines;
        } else {
            $fallback = BASE_PATH . '/lang/ar.php';
            self::$ar = is_file($fallback) ? (require $fallback) : [];
        }
    }

    public static function get(string $key, array $replace = []): string
    {
        $value = self::$lines[$key] ?? null;

        if (!is_string($value)) {
            // أي مفتاح غير مترجم نرجع إلى النص العربي
            $value = self::$ar[$key] ?? $key;
        }

        foreach ($replace as $search => $replacement) {
            $value = str_replace(':' . $search, (string) $replacement, $value);
        }

        return $value;
    }

    public static function locale(): string
    {
        return self::$locale;
    }

    public static function set(string $locale): void
    {
        Session::set('locale', $locale);
        self::load($locale);
    }
}