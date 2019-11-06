<?php

namespace Hypario\Router;

use Exception;
use Psr\Http\Message\ServerRequestInterface;

class Router
{
    /**
     * List of all the routes sorted by methods.
     *
     * @var array
     */
    private $routes = [
        'GET'    => [],
        'POST'   => [],
        'PUT'    => [],
        'DELETE' => []
    ];

    /**
     * list of all the named route with the name as key and an instance of Route as value.
     *
     * @var array
     */
    private $namedRoute = [];

    /**
     * parameters needed to generate the path of the named route.
     *
     * @var array
     */
    private $pathParams = [];

    /**
     * Add a route in GET method.
     *
     * @param string      $pattern
     * @param mixed       $handler
     * @param string|null $name
     */
    public function get(string $pattern, $handler, ?string $name = null): void
    {
        $this->addRoute('GET', $pattern, $handler, $name);
    }

    /**
     * Add a route in POST method.
     *
     * @param string      $pattern
     * @param mixed       $handler
     * @param string|null $name
     */
    public function post(string $pattern, $handler, ?string $name = null): void
    {
        $this->addRoute('POST', $pattern, $handler, $name);
    }

    /**
     * @param string      $pattern
     * @param mixed       $handler
     * @param string|null $name
     */
    public function put(string $pattern, $handler, ?string $name = null): void
    {
        $this->addRoute('PUT', $pattern, $handler, $name);
    }

    /**
     * @param string      $pattern
     * @param mixed       $handler
     * @param string|null $name
     */
    public function delete(string $pattern, $handler, ?string $name = null): void
    {
        $this->addRoute('DELETE', $pattern, $handler, $name);
    }

    /**
     * Return the route that matched or null if none matched.
     *
     * @param ServerRequestInterface|string $request
     *
     * @throws Exception
     *
     * @return Route|null
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
     *
     * @param string $name
     * @param array  $params
     * @param array  $queryParams
     *
     * @return string
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
     * return true if router have a named route.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasRoute(string $name)
    {
        return \array_key_exists($name, $this->namedRoute);
    }

    /**
     * The function that add the route in the chosen method.
     *
     * @param string      $method
     * @param string      $pattern
     * @param mixed       $handler
     * @param string|null $name
     *
     * @throws Exception
     */
    private function addRoute(string $method, string $pattern, $handler, ?string $name = null)
    {
        $this->routes[$method][$pattern] = new Route($pattern, $handler, $name);
        if (null !== $name) {
            $this->namedRoute[$name] = $this->routes[$method][$pattern];
        }
    }

    /**
     * Verify if the parameter match the pattern and replace it in the route pattern.
     *
     * @param $match
     *
     * @throws Exception
     *
     * @return string
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
