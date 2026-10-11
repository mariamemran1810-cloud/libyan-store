<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Errors;
use App\Core\Lang;

/**
 * فحص الدور: role:admin  أو  role:admin,seller
 */
final class RoleMiddleware
{
    public function handle(?string $argument = null): bool
    {
        if (!Auth::check()) {
            \flash('auth', Lang::get('errors.unauthorized'), 'error');
            redirect('/login');
        }

        $roles = array_filter(array_map('trim', explode(',', (string) $argument)));

        if ($roles === [] || Auth::roleIs($roles)) {
            return true;
        }

        http_response_code(403);
        header('Content-Type: text/html; charset=utf-8');
        echo '<!doctype html><html lang="ar" dir="rtl"><meta charset="utf-8"><title>'
            . e(Lang::get('errors.forbidden')) . '</title>'
            . '<body style="font-family:sans-serif;text-align:center;padding:60px">'
            . '<h1>' . e(Lang::get('errors.forbidden')) . '</h1>'
            . '<p>دورك الحالي: ' . e(Auth::role()) . '</p>'
            . '<p><a href="/">' . e(Lang::get('buttons.back_home')) . '</a></p>'
            . '</body></html>';
        exit;
    }
}