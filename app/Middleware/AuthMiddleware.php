<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Lang;

/**
 * لا يسمح بالدخول إلا لمن سجّل دخوله.
 */
final class AuthMiddleware
{
    public function handle(?string $argument = null): bool
    {
        if (Auth::check()) {
            return true;
        }

        \flash('auth', Lang::get('errors.unauthorized'), 'error');

        $wantsJson = str_contains((string) ($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json');
        if ($wantsJson) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => false, 'message' => Lang::get('errors.unauthorized')], JSON_UNESCAPED_UNICODE);
            exit;
        }

        redirect('/login');
    }
}