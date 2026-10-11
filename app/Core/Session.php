<?php

declare(strict_types=1);

namespace App\Core;

/**
 * إدارة جلسة المستخدم (الكوكيز الآمنة + الرسائل المؤقتة).
 */
final class Session
{
    private const FLASH = '_flash';

    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

        session_name(Env::get('SESSION_NAME', 'libyan_store_session'));
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();

        // تدوير الرسائل المؤقتة: ما كُتب في الطلب السابق يصبح معروضًا الآن مرة واحدة.
        $_SESSION[self::FLASH]['old'] = $_SESSION[self::FLASH]['new'] ?? [];
        $_SESSION[self::FLASH]['new'] = [];
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function flash(string $key, mixed $value): void
    {
        $_SESSION[self::FLASH]['new'][$key] = $value;
    }

    /** @return array<string,mixed> الرسائل المعروضة في الطلب الحالي */
    public static function flashData(): array
    {
        return $_SESSION[self::FLASH]['old'] ?? [];
    }

    public static function regenerate(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }
}