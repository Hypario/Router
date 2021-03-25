# A simple Router

[![Build Status](https://travis-ci.org/Hypario/Router.svg?branch=master)](https://travis-ci.org/Hypario/Router)
[![Coverage Status](https://coveralls.io/repos/github/Hypario/Router/badge.svg?branch=master)](https://coveralls.io/github/Hypario/Router?branch=master)

This Router is a project for people who want to understand how a router works, and it can be used to start a project really quickly without using any Framework such as Laravel or Symfony

## Installation

You can install this package using composer

```bash
composer require hypario/router
```

# How to use it

# basics
here we will talk about how to use the route for routes that doesn't need parameters

Like every other Router you have to initialize the Router, and write the routes you want like this :

```php
$router = new Hypario\Router(); // Here no parameters needed
$router->get('/', function () { echo "Hello World"; }); // Define a route in GET method.
```

There are other method you can choose such as the POST method

```php
$router = new Hypario\Router();
$router->post('/', function () { echo "Route accessed via POST method"; });
```

Careful ! those methods don't mean you can reach those pages, now you have to match your URL and the routes

# How to match the URL and the route

To match the route you have to use the match method from the router, it will return the route or null if none matched
```php
$router = new Hypario\Router();
$router->get('/', function () { echo "Hello World"; });

$route = $router->match($_SERVER['REQUEST_URI']);
```
here `$_SERVER['REQUEST_URI']` is used to get the URL, but you can use an object that implement the ServerRequestInterface from the PSR or a custom $_GET that give the URL

When you get the matched route, you can get the handler (here the function).
```php
$router = new Hypario\Router();
$router->get('/', function () { echo "Hello World"; });

$route = $router->match($_SERVER['REQUEST_URI']); // We get the matched route

if (!is_null($route)) {
    $function = $route->getHandler(); // We get the function
    call_user_func($function); // We call the function of the matched route
}
```
output : `Hello World` if the url is the main page of your website `www.mydomain.com`

The handler can be a string (like the name of a callable class) or a callable (like here, a function) as the router do not handle the way you're calling the handler.

# Routes with parameters

A route with parameters is a classical route, but whenever you want to add parameter,
it must be surrounded by `{}` and the name of the parameter and a pattern to match 
separated by `:` like below.

```php
$router->get('/hello/{name:[a-z]+}', function($name) { echo "Hello $name";});

$route = $router->match($_SERVER['REQUEST_URI']);

if (!is_null($route)) {
    $function = $route->getHandler();
    call_user_func_array($function, $route->getParams());
}
```

`{name:[a-z]+}` is one needed parameter of the route `/hello`, which `name` is the name of
the parameter, and `[a-z]+` is the pattern to match for the parameter.

# Named routes

The name of a route is just one more parameter to the method you want to create

```php
$router->get('/', 'handler', 'index'); # creates a route called index
```

I can now generate an uri to my index which will return `/`

```php
$pathToIndex = $router->getPath('index');
```

It also works for routes with parameters, you only need to add the array of parameters to the function

```php
$router->get('/articles/{id:[0-9]+}', 'handler', 'article');

$pathToArticle = $router->getPath('article', ["id" => 1]); 
// returns /articles/1
```

If two routes have the same name, it will generate the path of the LAST defined route

```php
$router->get('/', 'handler', 'index');
$router->get('/a', 'handler', 'index');

$pathToIndex = $router->getPath('index');
// returns /a
```
