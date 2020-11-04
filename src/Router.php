<?php

namespace Hypario;

use Exception;
use Psr\Http\Message\ServerRequestInterface;

class Router
{
    /**
     * List of all the routes sorted by methods.
     */
    private array $routes = [
        'GET'    => [],
        'POST'   => [],
        'PUT'    => [],
        'DELETE' => []
    ];

    /**
     * Needed to generate prefixed route.
     */
    private string $prefix = '';

    /**
     * list of all the named route with the name as key and an instance of Route as value.
     */
    private array $namedRoute = [];

    /**
     * parameters needed to generate the path of the named route.
     */
    private array $pathParams = [];

    /**
     * Add a route in GET method.
     *
     * @param mixed $handler
     */
    public function get(string $pattern, $handler, ?string $name = null): void
    {
        $this->addRoute('GET', $pattern, $handler, $name);
    }

    /**
     * Add a route in POST method.
     *
     * @param mixed $handler
     */
    public function post(string $pattern, $handler, ?string $name = null): void
    {
        $this->addRoute('POST', $pattern, $handler, $name);
    }

    /**
     * Add a route in PUT method.
     *
     * @param mixed $handler
     */
    public function put(string $pattern, $handler, ?string $name = null): void
    {
        $this->addRoute('PUT', $pattern, $handler, $name);
    }

    /**
     * Add a route in DELETE method.
     *
     * @param mixed $handler
     */
    public function delete(string $pattern, $handler, ?string $name = null): void
    {
        $this->addRoute('DELETE', $pattern, $handler, $name);
    }

    /**
     * Add a route in GET, POST, PUT and DELETE method.
     *
     * @param mixed $handler
     */
    public function any(string $pattern, $handler, ?string $name = null): void
    {
        $this->get($pattern, $handler, $name);
        $this->post($pattern, $handler, $name);
        $this->put($pattern, $handler, $name);
        $this->delete($pattern, $handler, $name);
    }

    /**
     * Add the routed specified in the callable with a prefix.
     */
    public function group(string $prefix, callable $callable): void
    {
        $this->prefix = $prefix;
        \call_user_func_array($callable, [$this]);
        $this->prefix = '';
    }

    /**
     * Return the route that matched or null if none matched.
     *
     * @param ServerRequestInterface|string $request
     */
    public function match($request): ?Route
    {
        if ($request instanceof ServerRequestInterface) {
            $url = $request->getUri()->getPath();
            $method = $request->getMethod();
        } elseif (\is_string($request)) {
            $url = $request;
            $method = $_SERVER['REQUEST_METHOD'];
        } else {
            throw new Exception('The request is not a string or an instance of ServerRequestInterface');
        }
        if (\array_key_exists($method, $this->routes)) {
            /** @var Route $route */
            foreach ($this->routes[$method] as $route) {
                if ($route->match($url)) {
                    return $route;
                }
            }
        } else {
            throw new Exception('Method not found');
        }

        return null;
    }

    /**
     * Return the url from the name of the route and the parameters.
     */
    public function getPath(string $name, array $params = [], array $queryParams = []): string
    {
        // if route exists
        if (\array_key_exists($name, $this->namedRoute)) {
            $pattern = $this->namedRoute[$name]->getPattern();
            if (!preg_match('#{.*?]#', $pattern)) {
                $path = '/' . $pattern;
            } else {
                $this->pathParams = $params; // params to replace
                // if the url need parameters, replace the pattern to given params
                $path = '/' . preg_replace_callback('#{.*?}#', [$this, 'replaceParams'], $pattern);
            }
            if (!empty($queryParams)) {
                return $path . '?' . http_build_query($queryParams);
            }

            return $path;
        }

        return '#';
    }

    /**
     * return true if router have a named route that matched that name.
     */
    public function hasRoute(string $name): bool
    {
        return \array_key_exists($name, $this->namedRoute);
    }

    /**
     * The function that add the route in the chosen method.
     *
     * @param mixed $handler
     * @param mixed $handler
     */
    private function addRoute(string $method, string $pattern, $handler, ?string $name = null)
    {
        // handle case we have a prefixed route
        $pattern = !empty($this->prefix) ? $this->prefix . '/' . trim($pattern, '/') : $pattern;

        $this->routes[$method][$pattern] = new Route($pattern, $handler, $name);
        if (null !== $name) {
            $this->namedRoute[$name] = $this->routes[$method][$pattern];
        }
    }

    /**
     * Verify if the parameter match the pattern and replace it in the route pattern.
     *
     * @param $match
     */
    private function replaceParams($match): string
    {
        // delete all the { and }
        $param = str_replace('}', '', str_replace('{', '', $match[0]));
        $parts = explode(':', $param);
        // if params match the pattern given
        if (\array_key_exists($parts[0], $this->pathParams)
            && preg_match("#$parts[1]#i", $this->pathParams[$parts[0]])
        ) {
            return $this->pathParams[$parts[0]];
        }
        throw new Exception('Parameters sent does not match the pattern');
    }
}
