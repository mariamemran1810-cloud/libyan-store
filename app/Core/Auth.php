<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\UserRepository;

/**
 * تسجيل الدخول والصلاحيات.
 */
final class Auth
{
    private const KEY = 'user_id';

    /** @var array<string,mixed>|null|null */
    private static ?array $user = null;
    private static bool $loaded = false;

    /** @return array<string,mixed>|null بيانات المستخدم الحالي */
    public static function user(): ?array
    {
        if (self::$loaded) {
            return self::$user;
        }

        self::$loaded = true;
        $id = Session::get(self::KEY);
        if (!is_int($id) && !is_numeric($id)) {
            self::$user = null;

            return null;
        }

        $user = UserRepository::find((int) $id);
        if ($user === null || (int) $user['is_active'] !== 1) {
            self::logout();
            self::$user = null;

            return null;
        }

        self::$user = $user;

        return $user;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function id(): ?int
    {
        $user = self::user();

        return $user === null ? null : (int) $user['id'];
    }

    public static function role(): string
    {
        $user = self::user();

        return $user === null ? 'guest' : (string) $user['role'];
    }

    /** هل الدور الحالي ضمن الأدوار المسموحة؟ role('admin') أو role(['admin','seller']) */
    public static function roleIs(string|array $roles): bool
    {
        $roles = (array) $roles;

        return in_array(self::role(), $roles, true);
    }

    /** محاولة دخول آمنة مع منع التخمين السريع */
    public static function attempt(string $email, string $password): bool
    {
        if (self::isThrottled()) {
            return false;
        }

        $user = UserRepository::findByEmail($email);

        if ($user === null || (int) $user['is_active'] !== 1 || !password_verify($password, $user['password_hash'])) {
            self::throttleHit();

            return false;
        }

        self::throttleReset();

        // تجديد معرّف الجلسة يمنع تثبيت الجلسة (Session Fixation)
        Session::regenerate();
        Session::set(self::KEY, (int) $user['id']);
        self::$user = null;
        self::$loaded = false;

        return true;
    }

    public static function login(int $userId): void
    {
        Session::regenerate();
        Session::set(self::KEY, $userId);
        self::$user = null;
        self::$loaded = false;
    }

    public static function logout(): void
    {
        Session::forget(self::KEY);
        Session::regenerate();
        self::$user = null;
        self::$loaded = false;
    }

    // ===== حظر محاولات الدخول المتكررة (5 محاولات كل 5 دقائق) =====

    public static function isThrottled(): bool
    {
        $hits = (array) Session::get('_login_hits', []);
        $recent = array_filter($hits, static fn (int $time): bool => $time > time() - 300);

        return count($recent) >= 5;
    }

    private static function throttleHit(): void
    {
        $hits = (array) Session::get('_login_hits', []);
        $hits[] = time();
        Session::set('_login_hits', array_slice($hits, -10));
    }

    private static function throttleReset(): void
    {
        Session::forget('_login_hits');
    }
}