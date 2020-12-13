<?php

namespace Test;

use GuzzleHttp\Psr7\ServerRequest;
use Hypario\Router;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    /**
     * @var Router
     */
    private $router;

    public function setUp(): void
    {
        $this->router = new Router();
    }

    public function testGetMethod()
    {
        $request = new ServerRequest('GET', '/blog');
        $this->router->get('/blog', function () {
            return 'Hello World !';
        }, 'blog');
        $this->router->post('/blog', function () {
        }, 'postBlog');
        $route = $this->router->match($request);
        $this->assertSame('blog', $route->getName());
        $this->assertSame('Hello World !', \call_user_func($route->getHandler()));
    }

    public function testPostMethod()
    {
        $request = new ServerRequest('POST', '/blog');
        $this->router->get('/blog', function () {
        }, 'getBlog');
        $this->router->post('/blog', function () {
            return 'Hello World !';
        }, 'postBlog');
        $route = $this->router->match($request);
        $this->assertSame('postBlog', $route->getName());
        $this->assertSame('Hello World !', \call_user_func($route->getHandler()));
    }

    public function testPutMethod()
    {
        $request = new ServerRequest('PUT', '/blog');
        $this->router->get('/blog', function () {
        }, 'getBlog');
        $this->router->put('/blog', function () {
            return 'Hello World !';
        }, 'postBlog');
        $route = $this->router->match($request);
        $this->assertSame('postBlog', $route->getName());
        $this->assertSame('Hello World !', \call_user_func($route->getHandler()));
    }

    public function testDeleteMethod()
    {
        $request = new ServerRequest('DELETE', '/blog');

        $this->router->get('/blog', function () {
        }, 'getBlog');

        $this->router->delete('/blog', function () {
            return 'Hello World !';
        }, 'postBlog');

        $route = $this->router->match($request);

        $this->assertSame('postBlog', $route->getName());
        $this->assertSame('Hello World !', \call_user_func($route->getHandler()));
    }

    public function testAnyMethod()
    {
        $request1 = new ServerRequest('GET', '/');
        $request2 = new ServerRequest('POST', '/');
        $request3 = new ServerRequest('PUT', '/');
        $request4 = new ServerRequest('DELETE', '/');
        $this->router->any('/', 'Hello', 'index');

        $route1 = $this->router->match($request1);
        $route2 = $this->router->match($request2);
        $route3 = $this->router->match($request3);
        $route4 = $this->router->match($request4);

        $this->assertSame('index', $route1->getName());
        $this->assertSame('Hello', $route1->getHandler());

        $this->assertSame('index', $route2->getName());
        $this->assertSame('Hello', $route2->getHandler());

        $this->assertSame('index', $route3->getName());
        $this->assertSame('Hello', $route3->getHandler());

        $this->assertSame('index', $route4->getName());
        $this->assertSame('Hello', $route4->getHandler());
    }

    public function testPrefixedRoute()
    {
        $this->router->group('/admin', function ($router) {
            /* @var Router $router */
            $router->get('/', 'Hello');
            $router->get('index', 'Hello');
        });

        $this->router->get('/', 'Hello World !');

        $request = new ServerRequest('GET', '/admin');
        $request2 = new ServerRequest('GET', '/admin/index');
        $request3 = new ServerRequest('GET', '/');

        $route = $this->router->match($request);
        $route2 = $this->router->match($request2);
        $route3 = $this->router->match($request3);

        $this->assertSame('admin', $route->getPattern());
        $this->assertSame('Hello', $route->getHandler());

        $this->assertSame('admin/index', $route2->getPattern());
        $this->assertSame('Hello', $route2->getHandler());

        $this->assertSame('', $route3->getPattern());
        $this->assertSame('Hello World !', $route3->getHandler());
    }

    public function testRouteAlreadyExist()
    {
        $this->router->get('/blog', function () {
            return 'I am blog';
        }, 'blog');
        $this->router->get('/aze', function () {
            return 'I am aze';
        }, 'blog');

        $request = new ServerRequest('GET', '/blog');
        $route = $this->router->match($request);

        $this->assertSame('blog', $route->getName());
        $this->assertSame('I am blog', $route->getHandler()());

        $url = $this->router->getPath('blog');

        // we get the last route called blog so careful
        $this->assertSame('/aze', $url);
    }

    public function testMatchMethod()
    {
        $this->router->get('/blog', function () {
            return 'Hello World !';
        });
        $this->router->get('/blog/{slug:[a-z]+}', function () {
            return 'aze';
        });
        // with a string as url
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/blog';
        $route = $this->router->match($_SERVER['REQUEST_URI']);
        $this->assertSame('blog', $route->getPattern());
        $this->assertSame('Hello World !', \call_user_func($route->getHandler()));

        // with an object that implement the ServerRequestInterface
        $request = new ServerRequest('GET', '/blog/aze');
        $route = $this->router->match($request);
        $this->assertSame('aze', \call_user_func($route->getHandler()));
    }

    public function testMatchMethodWithBadRequest()
    {
        $this->expectException(\Exception::class);
        $request = [];
        $this->router->match($request);
    }

    public function testIfUrlDoesNotExist()
    {
        // Test with GET Method
        $request = new ServerRequest('GET', '/azeaze');

        $route = $this->router->match($request);
        $this->assertNull($route);

        // Test with POST Method
        $request = new ServerRequest('POST', '/azeaze');

        $route = $this->router->match($request);
        $this->assertNull($route);
    }

    public function testGetMethodWithParameters()
    {
        $request = new ServerRequest('GET', '/blog/mon-slug-8');

        $this->router->get('/blog/{slug:[a-z0-9\-]+}-{id:\d+}', function () {
            return 'hello';
        }, 'post.show');

        $route = $this->router->match($request);
        $this->assertSame('post.show', $route->getName());
        $this->assertSame('hello', \call_user_func($route->getHandler()));
        $this->assertSame(['slug' => 'mon-slug', 'id' => '8'], $route->getParams());

        // Test invalid url with parameters
        $request = new ServerRequest('GET', '/blog/mon_slug-8');
        $route = $this->router->match($request);
        $this->assertNull($route);
    }

    public function testGenerateUri()
    {
        // test without parameters
        $this->router->get('/blog', function () {
        }, 'blog');
        $uri = $this->router->getPath('blog');

        $this->assertSame('/blog', $uri);

        // test with parameters
        $this->router->get('/blog/{slug:[a-z0-9\-]+}-{id:\d+}', function () {
        }, 'post.show');
        $uri = $this->router->getPath(
            'post.show',
            ['slug' => 'mon-article', 'id' => 18]
        );
        $this->assertSame('/blog/mon-article-18', $uri);

        $uri = $this->router->getPath(
            'post.show',
            ['id' => 18, 'slug' => 'mon-article']
        );
        $this->assertSame('/blog/mon-article-18', $uri);

        // test with queryParams
        $uri = $this->router->getPath(
            'post.show',
            ['slug' => 'mon-article', 'id' => 18],
            ['p'    => 2]
        );
        $this->assertSame('/blog/mon-article-18?p=2', $uri);
    }

    public function testGenerateUriWithFalseParameters()
    {
        $this->router->get('/blog/{slug:[a-z0-9\-]+}-{id:\d+}', function () {
        }, 'post.show');
        $this->expectException(\Exception::class);
        $this->router->getPath(
            'post.show',
            ['azeaze' => 'azeaze']
        );
    }

    public function testGenerateUriWithBadName()
    {
        $url = $this->router->getPath('azeaze');
        $this->assertSame('#', $url);
    }

    public function testWrongMethod()
    {
        $request = new ServerRequest('AZEAZE', '/');
        $this->expectException(\Exception::class);
        $this->router->match($request);
    }

    public function testHasRoute()
    {
        $this->router->get('/', function () {
            return 'Hello';
        }, 'hello');

        $this->assertTrue($this->router->hasRoute('hello'));
        $this->assertFalse($this->router->hasRoute('helloWorld'));
    }
}
