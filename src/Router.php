<?php
declare(strict_types=1);

namespace Ulyssear;

class Router
{

    private static Collection $routes;

    public static function __callStatic(string $method, array $parameters)
    {
        if (!method_exists(__CLASS__, $method)) return;

        if (!isset(self::$routes)) {
            self::$routes = (new Collection)
                ->setFunction('getByName', function ($name) {
                    foreach (self::$routes->entries() as $method => $routes) {
                        foreach ($routes->entries() as $route) {
                            if ($name === $route->name()) {
                                return $route;
                            }
                        }
                    }
                    throw new \Exception('Route inconnue');
                })
                ->setFunction('getByURI', function ($uri) use ($method) {
                    $result = new Collection;
                    foreach (self::$routes->entries() as $method => $routes) {
                        foreach ($routes->entries() as $route) {
                            if ($uri === $route->uri()) {
                                $result->pushNamedItem($method, $route);
                            }
                        }
                    }
                    if (0 < $result->count()) return $result;
                    throw new \Exception('Route inconnue');
                });
        }

        return forward_static_call_array([__CLASS__, $method], $parameters);
    }

    private static function currentRoute()
    {
        try {
            list('REQUEST_METHOD' => $method, 'REQUEST_URI' => $uri) = $_SERVER;
        } catch (\Throwable $exception) {
            throw new \Exception(<<<EOL
            Utilisez-vous un navigateur ? Si ce n\'est pas le cas, télécharger Google 
            Chrome, Mozilla Firefox ou Microsoft Edge.
            EOL, $exception);
        }

        return self::$routes->get($method)->getByURI($uri) ?? 'ajouter 404';
    }

    private static function route(string $method, string $uri, $callback, ?string $name = null, array $data = [])
    {
        if (!self::$routes->hasIndex($method)) {
            self::$routes->pushNamedItem($method, (new Collection)
                ->setFunction('getByName', function ($name) use ($method) {
                    foreach (self::$routes->get($method)->entries() as $route) {
                        if ($name === $route->name()) {
                            return $route;
                        }
                    }
                    throw new \Exception('Route inconnue');
                })
                ->setFunction('getByURI', function ($uri) use ($method) {
                    foreach (self::$routes->get($method)->entries() as $route) {
                        if ($uri === $route->uri()) {
                            return $route;
                        }
                    }
                    throw new \Exception('Route inconnue');
                })
            );
        }

        if (!self::$routes->get($method)->hasIndex($uri)) {
            self::$routes->get($method)->pushNamedItem($uri, new Route($method, $uri, $callback, $name, $data));
            return true;
        }

        return false;
    }

    private static function get(string $uri, $callback, ?string $name = null, array $data = []) {
        return self::route('GET', $uri, $callback, $name, $data);
    }

    private static function post(string $uri, $callback, ?string $name = null, array $data = []) {
        return self::route('POST', $uri, $callback, $name, $data);
    }

    private static function routes()
    {
        return self::$routes;
    }

    private static function render() {
        $current = self::currentRoute();

        $body = [
            'POST' => $_POST ?? [],
            'GET' => $_GET ?? [],
        ];

        $current->callback()(
            new Collection($body),
            new Response(200), ...$current->data()->entries()
        );
        return true;
    }

    private static function view(string $uri, string $view) {
        echo include_once(__DIR__ . "/../views/$view.php");
    }

}