# A simple Router

[![Build Status](https://travis-ci.org/Hypario/Router.svg?branch=master)](https://travis-ci.org/Hypario/Router)
[![Coverage Status](https://coveralls.io/repos/github/Hypario/Router/badge.svg?branch=master)](https://coveralls.io/github/Hypario/Router?branch=master)

This Router is a project for people who wants to understand how a router works and it can be used to start a project really quickly without using any Framework such as Laravel or Symfony

## Installation

I recommend you to use composer to download this project

```bash
composer require hypario/router
```

or you can download the zip file or clone the project and copy/paste it in your project (not recommended)

# How to use it

# basics
here we will talk about how to use the route for routes that doesn't need parameters

Like every other Router you have to intialize the Router, and write the routes you want like this :

```php
$router = new Hypario\Router(); // Here no parameters needed
$router->get('/', function () { echo "Hello World"; }); // Define a route in GET method.
```

There are other method you can choose such as the POST method

```php
$router = new Hypario\Router();
$router->post('/', function () { echo "Route accessed via POST method"; });
```

Carefull ! those methods doesn't mean you can reach those pages, now you have to match your URL and the routes

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
output : `Hello World` if the url is simply the main page of your website `www.mydomain.com`

The handler can be a string (like the name of a callable class) or a callable (like here, a function) as the router do not handle the way you're calling the handler.

# complex pattern for routes

now we will see how we can create complex routes (with parameters).

Every parameter must be surrounded by brackets and separated by `:`.

```php
$router->get('/hello/{name:[a-z]+}', function($name) { echo "Hello $name";});
$route = $router->match($_SERVER['REQUEST_URI']);

if (!is_null($route)) {
    $function = $route->getHandler();
    call_user_func_array($function, $route->getParams());
}
```
The first part (here name) is the name of the variable, and the second part is the pattern to match for the part after `/hello/`.
The variable will be passed as a parameter to the function because of the $route->getParams(), The route contains every parameters that matched with the route.
