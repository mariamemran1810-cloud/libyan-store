<?php

declare(strict_types=1);

namespace App\Core;

use Throwable;

/**
 * معالجة الأخطاء: عرض صفحة عربية مفهومة + تسجيل التفاصيل في ملف السجل.
 */
final class Errors
{
    public static function register(): void
    {
        set_error_handler(static function (int $level, string $message, string $file = '', int $line = 0): bool {
            if (!(error_reporting() & $level)) {
                return false;
            }
            throw new \ErrorException($message, 0, $level, $file, $line);
        });

        set_exception_handler(static function (Throwable $e): void {
            self::log($e);
            self::render($e);
        });

        register_shutdown_function(static function (): void {
            $error = error_get_last();
            if ($error === null || !in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
                return;
            }
            $e = new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']);
            self::log($e);
            self::render($e);
        });
    }

    public static function log(Throwable $e): void
    {
        $dir = BASE_PATH . '/storage/logs';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        $line = sprintf(
            "[%s] %s: %s in %s:%d\n%s\n\n",
            date('Y-m-d H:i:s'),
            $e::class,
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );

        @file_put_contents($dir . '/app.log', $line, FILE_APPEND | LOCK_EX);
    }

    public static function render(Throwable $e): void
    {
        $wantsJson = str_contains((string) ($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json');
        $debug = Env::bool('APP_DEBUG');
        $message = $debug ? $e->getMessage() : Lang::get('errors.500_message');

        if ($wantsJson) {
            if (!headers_sent()) {
                http_response_code(500);
                header('Content-Type: application/json; charset=utf-8');
            }
            echo json_encode(['ok' => false, 'message' => $message], JSON_UNESCAPED_UNICODE);
            return;
        }

        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: text/html; charset=utf-8');
        }

        $view = BASE_PATH . '/views/errors/500.php';
        if (is_file($view)) {
            $error = $e;
            $messageText = $message;
            $showDetails = $debug;
            include $view;
            return;
        }

        echo '<h1>' . e(Lang::get('errors.500_title')) . '</h1><p>' . e($message) . '</p>';
    }

    public static function notFoundPage(): void
    {
        if (!headers_sent()) {
            http_response_code(404);
            header('Content-Type: text/html; charset=utf-8');
        }

        $path = (string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);

        View::render('errors/404', [
            'page' => [
                'title' => Lang::get('errors.404_title'),
                'description' => Lang::get('errors.404_message'),
                'canonical' => $path,
                'noindex' => true,
            ],
        ]);
    }
}