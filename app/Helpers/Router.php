<?php

namespace App\Helpers;

class Router
{
    private array $routes = [];

    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    public function dispatch(string $method, string $uri): void
    {
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rtrim($uri, '/') ?: '/';

        foreach ($this->routes as $route => $handler) {
            [$routeMethod, $routePath] = explode(' ', $route, 2);

            if (strtoupper($routeMethod) !== strtoupper($method)) {
                continue;
            }

            $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '([^/]+)', $routePath);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);

                [$controllerName, $action] = $handler;
                $controllerClass = "App\\Controllers\\{$controllerName}";

                if (!class_exists($controllerClass)) {
                    http_response_code(500);
                    echo "Controller not found: {$controllerClass}";
                    return;
                }

                $controller = new $controllerClass();

                if (!method_exists($controller, $action)) {
                    http_response_code(500);
                    echo "Action not found: {$action}";
                    return;
                }

                call_user_func_array([$controller, $action], $matches);
                return;
            }
        }

        http_response_code(404);
        echo "404 - Página não encontrada";
    }
}
