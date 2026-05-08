<?php

declare(strict_types=1);

namespace App\Http;

/**
 * Minimum router — Faz 0.5 demo için. Faz 1.0a'da Laravel router'a taşınır.
 * Path bazlı eşleştirme + (isteğe bağlı) middleware listesi.
 */
final class Router
{
    /** @var array<int, array{method:string, pattern:string, handler:callable, middlewares:array<int,callable>}> */
    private array $routes = [];

    public function add(string $method, string $pattern, callable $handler, array $middlewares = []): void
    {
        $this->routes[] = [
            'method'      => strtoupper($method),
            'pattern'     => $pattern,
            'handler'     => $handler,
            'middlewares' => $middlewares,
        ];
    }

    public function get(string $pattern, callable $handler, array $middlewares = []): void
    {
        $this->add('GET', $pattern, $handler, $middlewares);
    }

    public function post(string $pattern, callable $handler, array $middlewares = []): void
    {
        $this->add('POST', $pattern, $handler, $middlewares);
    }

    public function dispatch(string $method, string $uri): void
    {
        $method = strtoupper($method);
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        // Trailing slash normalize: '/foo/' -> '/foo', ama '/' aynı kalır.
        if ($path !== '/' && str_ends_with($path, '/')) {
            $path = rtrim($path, '/');
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $params = [];
            if (!$this->match($route['pattern'], $path, $params)) {
                continue;
            }

            // Middleware zincirini çalıştır (her biri durdurabilir).
            foreach ($route['middlewares'] as $mw) {
                $result = $mw();
                if ($result === false) {
                    return;  // middleware redirect veya 403 vermiş olur
                }
            }

            $response = ($route['handler'])($params);
            if (is_string($response)) {
                echo $response;
            }
            return;
        }

        $this->notFound();
    }

    /**
     * Pattern eşleştirme — `/foo/{id}` desenli; `{id}` paramı yakalar.
     */
    private function match(string $pattern, string $path, array &$params): bool
    {
        $regex = preg_replace('#\{([a-zA-Z_]+)\}#', '(?P<$1>[^/]+)', $pattern);
        // Pattern '/' aynen kalmalı; diğer trailing '/' temizlenir.
        if ($regex !== '/' && str_ends_with($regex, '/')) {
            $regex = rtrim($regex, '/');
        }
        $regex = '#^' . $regex . '$#u';

        if (!preg_match($regex, $path, $matches)) {
            return false;
        }

        foreach ($matches as $key => $value) {
            if (!is_int($key)) {
                $params[$key] = $value;
            }
        }
        return true;
    }

    private function notFound(): void
    {
        http_response_code(404);
        echo \view('errors.404');
    }
}
