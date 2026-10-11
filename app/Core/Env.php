<?php

declare(strict_types=1);

namespace App\Core;

/**
 * قارئ ملف الإعدادات .env
 * القاعدة: كل الأسرار (كلمات المرور، المفاتيح) توضع في .env فقط وليس في الكود.
 */
final class Env
{
    /** @var array<string,string> */
    private static array $values = [];

    public static function load(string $path): void
    {
        if (!is_file($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            $pos = strpos($line, '=');
            if ($pos === false) {
                continue;
            }

            $key = trim(substr($line, 0, $pos));
            $value = trim(substr($line, $pos + 1));

            if ($value !== '' && $value[0] === $value[strlen($value) - 1] && in_array($value[0], ['"', "'"], true)) {
                $value = substr($value, 1, -1);
            }

            self::$values[$key] = $value;
        }
    }

    public static function get(string $key, string $default = ''): string
    {
        return self::$values[$key] ?? $default;
    }

    public static function int(string $key, int $default = 0): int
    {
        if (!isset(self::$values[$key]) || !is_numeric(self::$values[$key])) {
            return $default;
        }

        return (int) self::$values[$key];
    }

    public static function bool(string $key, bool $default = false): bool
    {
        if (!isset(self::$values[$key])) {
            return $default;
        }

        return in_array(strtolower(self::$values[$key]), ['1', 'true', 'yes', 'on'], true);
    }

    public static function has(string $key): bool
    {
        return isset(self::$values[$key]) && self::$values[$key] !== '';
    }
}