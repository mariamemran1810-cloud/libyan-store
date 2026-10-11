<?php

declare(strict_types=1);

namespace App\Core;

/**
 * الموجّه (Router): يربط الرابط بالوظيفة المناسبة.
 *
 * $router->get('/product/{slug}', [ProductController::class, 'show']);
 * $router->post('/cart/add', [CartController::class, 'add'], ['auth']);
 */
final class Router
{
    /** @var array<int,array{method:string,regex:string,params:array<string>,handler:mixed,middleware:array<string>}> */
    private array $routes = [];

    public function get(string $path, mixed $handler, array $middleware = []): void
    {
        $this->add('GET', $path, $handler, $middleware);
    }

    public function post(string $path, mixed $handler, array $middleware = []): void
    {
        $this->add('POST', $path, $handler, $middleware);
    }

    public function put(string $path, mixed $handler, array $middleware = []): void
    {
        $this->add('PUT', $path, $handler, $middleware);
    }

    public function delete(string $path, mixed $handler, array $middleware = []): void
    {
        $this->add('DELETE', $path, $handler, $middleware);
    }

    private function add(string $method, string $path, mixed $handler, array $middleware): void
    {
        $params = [];
        $regex = preg_replace_callback(
            '/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/',
            static function (array $match) use (&$params): string {
                $params[] = $match[1];
                return '([^/]+)';
            },
            $path
        );

        $this->routes[] = [
            'method' => strtoupper($method),
            'regex' => '#^' . $regex . '$#u',
            'params' => $params,
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = (string) parse_url($uri, PHP_URL_PATH);
        $path = rawurldecode($path);
        $path = '/' . ltrim($path, '/');
        if ($path !== '/') {
            $path = rtrim($path, '/');
        }

        $method = strtoupper($method);

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            if (!preg_match($route['regex'], $path, $matches)) {
                continue;
            }

            array_shift($matches);
            $args = array_map(static fn (string $value): string => rawurldecode($value), $matches);

            if (!$this->runMiddleware($route['middleware'])) {
                return;
            }

            $this->call($route['handler'], $args);
            return;
        }

        Errors::notFoundPage();
    }

    /** @param array<string> $names */
    private function runMiddleware(array $names): bool
    {
        foreach ($names as $name) {
            [$key, $argument] = array_pad(explode(':', $name, 2), 2, null);
            // أول حرف كبير: guest → GuestMiddleware (مهم على لينكس التي تفرّق بين الأحرف)
            $class = 'App\\Middleware\\' . ucfirst($key) . 'Middleware';

            if (!class_exists($class)) {
                throw new \RuntimeException('Middleware غير موجود: ' . $class);
            }

            if (!(new $class())->handle($argument)) {
                return false;
            }
        }

        return true;
    }

    private function call(mixed $handler, array $args): void
    {
        if (is_array($handler) && count($handler) === 2) {
            [$class, $method] = $handler;
            (new $class())->{$method}(...$args);
            return;
        }

        if (is_callable($handler)) {
            $handler(...$args);
            return;
        }

        throw new \RuntimeException('مسار غير صالح في التوجيه.');
    }
}