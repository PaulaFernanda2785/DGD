<?php

declare(strict_types=1);

namespace App\Core;

use Closure;
use RuntimeException;

class Router
{
    private array $routes = [];

    public function __construct(
        private readonly Request $request,
        private readonly Response $response
    ) {
    }

    public function add(string $method, string $path, array|callable $handler, array $middlewares = []): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => '/' . trim($path, '/'),
            'handler' => $handler,
            'middlewares' => $middlewares,
        ];
    }

    public function dispatch(): void
    {
        $method = $this->request->method();
        $path = $this->request->path();

        foreach ($this->routes as $route) {
            $params = $this->match($route['path'], $path);

            if ($params === null || $route['method'] !== $method) {
                continue;
            }

            $this->runMiddlewares($route['middlewares'], $params);
            $this->runHandler($route['handler'], $params);
            return;
        }

        throw new HttpException(404, 'Pagina nao encontrada.');
    }

    private function match(string $routePath, string $requestPath): ?array
    {
        $pattern = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';

        if (!preg_match($pattern, $requestPath, $matches)) {
            return null;
        }

        return array_filter(
            $matches,
            static fn (string|int $key): bool => is_string($key),
            ARRAY_FILTER_USE_KEY
        );
    }

    private function runMiddlewares(array $middlewares, array $params): void
    {
        foreach ($middlewares as $middleware) {
            if (is_array($middleware)) {
                [$class, $argument] = $middleware;
                $instance = new $class($argument);
            } else {
                $instance = new $middleware();
            }

            if (!method_exists($instance, 'handle')) {
                throw new RuntimeException('Middleware invalido.');
            }

            $instance->handle($this->request, $this->response, $params);
        }
    }

    private function runHandler(array|callable $handler, array $params): void
    {
        if ($handler instanceof Closure || is_callable($handler)) {
            $handler($this->request, $this->response, $params);
            return;
        }

        [$class, $method] = $handler;

        if (!class_exists($class)) {
            throw new HttpException(500, 'Controller nao encontrado: ' . $class);
        }

        $controller = new $class($this->request, $this->response);

        if (!method_exists($controller, $method)) {
            throw new HttpException(500, 'Acao nao encontrada: ' . $method);
        }

        $controller->{$method}(...array_values($params));
    }
}
