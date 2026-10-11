<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;

/**
 * يمنع المستخدم المسجّل من زيارة صفحات الدخول/التسجيل.
 */
final class GuestMiddleware
{
    public function handle(?string $argument = null): bool
    {
        if (!Auth::check()) {
            return true;
        }

        redirect(Auth::roleIs(['admin', 'seller']) ? '/admin' : '/account');
    }
}