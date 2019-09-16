<?php

namespace Test;

use GuzzleHttp\Psr7\ServerRequest;
use Hypario\Router;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class RouterTest extends TestCase
{
    /**
     * @var Router
     */
    private $router;

    public function setUp()
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

    public function testRouteAlreadyExist()
    {
        $this->router->get('/blog', function () {
        }, 'blog');
        $this->expectException(\Exception::class);
        $this->router->get('/aze', function () {
        }, 'blog');
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
        $this->expectException(\Exception::class);
        $this->router->getPath('azeaze');
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
            return "Hello";
        }, 'hello');
        $this->assertTrue($this->router->hasRoute('hello'));
        $this->assertFalse($this->router->hasRoute('helloWorld'));
    }
}
