<?php

namespace App\Core;

class Router
{
    private array $routes = [];
    private string $groupPrefix = '';
    /** @var array<int, callable> */
    private array $groupMiddleware = [];

    public function get(string $path, array|callable $handler, array $middleware = []): void
    {
        $this->add('GET', $path, $handler, $middleware);
    }

    public function post(string $path, array|callable $handler, array $middleware = []): void
    {
        $this->add('POST', $path, $handler, $middleware);
    }

    public function put(string $path, array|callable $handler, array $middleware = []): void
    {
        $this->add('PUT', $path, $handler, $middleware);
    }

    public function delete(string $path, array|callable $handler, array $middleware = []): void
    {
        $this->add('DELETE', $path, $handler, $middleware);
    }

    public function group(string $prefix, array $middleware, callable $callback): void
    {
        $previousPrefix = $this->groupPrefix;
        $previousMiddleware = $this->groupMiddleware;

        $this->groupPrefix = $previousPrefix . $prefix;
        $this->groupMiddleware = [...$previousMiddleware, ...$middleware];

        $callback($this);

        $this->groupPrefix = $previousPrefix;
        $this->groupMiddleware = $previousMiddleware;
    }

    private function add(string $method, string $path, array|callable $handler, array $middleware): void
    {
        $fullPath = rtrim($this->groupPrefix . $path, '/') ?: '/';
        $this->routes[] = [
            'method' => $method,
            'path' => $fullPath,
            'handler' => $handler,
            'middleware' => [...$this->groupMiddleware, ...$middleware],
            'regex' => $this->toRegex($fullPath),
        ];
    }

    private function toRegex(string $path): string
    {
        $pattern = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    public function dispatch(Request $request): void
    {
        $method = $request->method();
        $path = $request->path();

        $allowedMethods = [];

        foreach ($this->routes as $route) {
            if (preg_match($route['regex'], $path, $matches)) {
                if ($route['method'] !== $method) {
                    $allowedMethods[] = $route['method'];
                    continue;
                }

                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $req = new Request($params);

                $next = function (Request $req) use ($route) {
                    $handler = $route['handler'];
                    if (is_callable($handler) && !is_array($handler)) {
                        return $handler($req);
                    }
                    [$class, $methodName] = $handler;
                    $controller = new $class();
                    return $controller->$methodName($req);
                };

                foreach (array_reverse($route['middleware']) as $mw) {
                    $next = fn(Request $req) => $mw($req, $next);
                }

                $next($req);
                return;
            }
        }

        if ($allowedMethods) {
            http_response_code(405);
            echo '405 Method Not Allowed';
            return;
        }

        http_response_code(404);
        $view = new View();
        echo $view->render('errors.404', []);
    }
}
