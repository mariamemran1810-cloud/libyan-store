<?php

declare(strict_types=1);

/**
 * نقطة الدخول الوحيدة للمشروع (Front Controller).
 * كل الطلبات تمر من هنا: صفحات، نماذج، وإدارة.
 */

// في وضع php -S نترك السيرفر يحمّل الملفات الثابتة (CSS/JS/صور) بنفسه.
if (PHP_SAPI === 'cli-server') {
    $requested = __DIR__ . (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    if (is_file($requested)) {
        return false;
    }
}

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/app/bootstrap.php';

$router = new App\Core\Router();
$registerRoutes = require BASE_PATH . '/app/routes.php';
$registerRoutes($router);

$router->dispatch(
    (string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'),
    (string) ($_SERVER['REQUEST_URI'] ?? '/')
);