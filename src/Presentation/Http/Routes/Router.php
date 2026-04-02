<?php

declare(strict_types=1);

namespace App\Presentation\Http\Routes;

use App\Presentation\Http\Responses\JsonResponse;

final class Router
{
    private array $routes = [];

    public function __construct(
        private readonly string $basePath = ''
    ) {}

    public function get(string $path, callable $handler): self
    {
        return $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): self
    {
        return $this->addRoute('POST', $path, $handler);
    }

    public function put(string $path, callable $handler): self
    {
        return $this->addRoute('PUT', $path, $handler);
    }

    public function delete(string $path, callable $handler): self
    {
        return $this->addRoute('DELETE', $path, $handler);
    }

    public function dispatch(string $method, string $uri): void
    {
        $uri = $this->normalizePath($uri);

        foreach ($this->routes[$method] ?? [] as $route) {
            if (preg_match($route['pattern'], $uri, $matches)) {
                $params = array_filter($matches, fn($key) => is_string($key), ARRAY_FILTER_USE_KEY);
                call_user_func_array($route['handler'], $params);
                return;
            }
        }

        JsonResponse::error('Route not found', 404);
    }

    private function addRoute(string $method, string $path, callable $handler): self
    {
        $this->routes[$method][] = [
            'pattern' => $this->pathToRegex($path),
            'handler' => $handler,
        ];
        return $this;
    }

    private function pathToRegex(string $path): string
    {
        $path = $this->normalizePath($path);
        $escaped = preg_quote($path, '#');
        $regex = preg_replace('/\\\{(\w+)\\\}/', '(?P<$1>[^/]+)', $escaped);
        return '#^' . $regex . '$#';
    }

    private function normalizePath(string $path): string
    {
        $path = parse_url($path, PHP_URL_PATH) ?? '/';

        if ($this->basePath !== '' && str_starts_with($path, $this->basePath)) {
            $path = substr($path, strlen($this->basePath));
        }

        return '/' . trim($path, '/');
    }
}
