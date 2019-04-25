<?php

require 'vendor/autoload.php';

$router = new \Hypario\Router();

$router->get('/', function () {
    echo "Hello World !";
});

$router->get('/{name:[a-zA-Z-]+}-{id:[0-9]+}', function ($name, $id) {
    echo "Hello $name ! Tu as l'id $id";
});

$route = $router->match($_SERVER['REQUEST_URI']);

if (!is_null($route)) {
    call_user_func_array($route->getHandler(), $route->getParams());
}
