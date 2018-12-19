# A simple Router

This Router is a project for people who wants to understand how a router works and it can be used to start a project really quickly without using any Framework such as Laravel or Symfony

## Installation

I recommend you to use composer to download this project

```bash
composer require hypario/router
```

or you can download the zip file or clone the project and copy/paste it in your project (not recommended)

# How to use it

Like every other Router you have to intialize the Router, and write the routes you want like this :

```php
$router = new Hypario\Router(); // Here no parameters needed
$router->get('/', function () { echo "Hello World"; }); // Define a route in GET method, when called will call the function in second parameter
```

