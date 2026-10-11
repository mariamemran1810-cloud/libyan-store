<?php

declare(strict_types=1);

namespace App\Core;

/**
 * تقييد معدل الطلبات (حماية من الإرسال المتكرر/الروبوتات).
 * محفوظ في الجلسة، بسيط وسريع.
 */
final class Throttle
{
    /**
     * يسجّل محاولة ويعيد true إذا كانت مسموحة، false إذا تجاوزت الحد.
     *
     * مثال: Throttle::attempt('checkout', 5, 60) = خمس محاولات كحد أقصى كل دقيقة
     */
    public static function attempt(string $key, int $max, int $windowSeconds): bool
    {
        $key = '_throttle_' . $key;
        $hits = array_filter(
            (array) Session::get($key, []),
            static fn (int $time): bool => $time > time() - $windowSeconds
        );

        if (count($hits) >= $max) {
            Session::set($key, $hits);

            return false;
        }

        $hits[] = time();
        Session::set($key, $hits);

        return true;
    }

    public static function reset(string $key): void
    {
        Session::forget('_throttle_' . $key);
    }
}