<?php
/**
 * Router
 */

class Router {
    private static $routes = [];
    private static $middleware = [];

    public static function get(string $path, string $controller, string $method): void {
        self::$routes['GET'][$path] = ['controller' => $controller, 'method' => $method];
    }

    public static function post(string $path, string $controller, string $method): void {
        self::$routes['POST'][$path] = ['controller' => $controller, 'method' => $method];
    }

    public static function middleware(string $name, callable $callback): void {
        self::$middleware[$name] = $callback;
    }

    public static function dispatch(): void {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Strip base path prefix
        $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        if ($basePath !== '' && strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }

        $uri = rtrim($uri, '/') ?: '/';

        // Check for static assets
        if (preg_match('/\.(css|js|jpg|jpeg|png|gif|ico|svg|woff|woff2|ttf|eot)$/', $uri)) {
            return;
        }

        // Exact match
        if (isset(self::$routes[$method][$uri])) {
            $route = self::$routes[$method][$uri];
            self::callRoute($route);
            return;
        }

        // Pattern matching
        if (isset(self::$routes[$method])) {
            foreach (self::$routes[$method] as $pattern => $route) {
                $regex = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $pattern);
                $regex = '#^' . $regex . '$#';
                if (preg_match($regex, $uri, $matches)) {
                    $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                    $_GET = array_merge($_GET, $params);
                    self::callRoute($route);
                    return;
                }
            }
        }

        http_response_code(404);
        require_once __DIR__ . '/views/errors/404.php';
    }

    private static function callRoute(array $route): void {
        $controllerClass = $route['controller'];
        $method = $route['method'];

        $controller = new $controllerClass();

        if (method_exists($controller, $method)) {
            $controller->$method();
        } else {
            http_response_code(500);
            echo "Method {$method} not found in {$controllerClass}";
        }
    }
}
