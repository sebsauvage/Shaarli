<?php

declare(strict_types=1);

namespace Shaarli\Front;

use PHPUnit\Framework\TestCase;
use Shaarli\Config\ConfigManager;
use Shaarli\Container\ShaarliContainer;
use Shaarli\Front\Exception\LoginBannedException;
use Shaarli\Front\Exception\UnauthorizedException;
use Shaarli\Render\PageBuilder;
use Shaarli\Render\PageCacheManager;
use Shaarli\Security\LoginManager;
use Shaarli\Updater\Updater;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\Uri;

class ShaarliMiddlewareTest extends TestCase
{
    /** @var ShaarliContainer */
    protected $container;

    /** @var ShaarliMiddleware  */
    protected $middleware;

    public function setUp(): void
    {
        $this->container = $this->createMock(ShaarliContainer::class);

        $this->container->conf = $this->createMock(ConfigManager::class);
        $this->container->loginManager = $this->createMock(LoginManager::class);

        $this->middleware = new ShaarliMiddleware($this->container);
    }

    /**
     * Test middleware execution with valid controller call
     */
    public function testMiddlewareExecution(): void
    {
        $request = $this->createMock(Request::class);
        $request->method('getUri')->willReturnCallback(function (): Uri {
            $uri = $this->createMock(Uri::class);
            $uri->method('getBasePath')->willReturn('/subfolder');

            return $uri;
        });

        $response = new Response();
        $controller = function (Request $request, Response $response): Response {
            return $response->withStatus(418); // I'm a tea pot
        };

        /** @var Response $result */
        $result = $this->middleware->__invoke($request, $response, $controller);

        static::assertInstanceOf(Response::class, $result);
        static::assertSame(418, $result->getStatusCode());
    }

    /**
     * Test middleware execution with controller throwing a known front exception
     */
    public function testMiddlewareExecutionWithFrontException(): void
    {
        $request = $this->createMock(Request::class);
        $request->method('getUri')->willReturnCallback(function (): Uri {
            $uri = $this->createMock(Uri::class);
            $uri->method('getBasePath')->willReturn('/subfolder');

            return $uri;
        });

        $response = new Response();
        $controller = function (): void {
            $exception = new LoginBannedException();

            throw new $exception;
        };

        $pageBuilder = $this->createMock(PageBuilder::class);
        $pageBuilder->method('render')->willReturnCallback(function (string $message): string {
            return $message;
        });
        $this->container->pageBuilder = $pageBuilder;

        /** @var Response $result */
        $result = $this->middleware->__invoke($request, $response, $controller);

        static::assertInstanceOf(Response::class, $result);
        static::assertSame(401, $result->getStatusCode());
        static::assertContains('error', (string) $result->getBody());
    }

    /**
     * Test middleware execution with controller throwing a not authorized exception
     */
    public function testMiddlewareExecutionWithUnauthorizedException(): void
    {
        $request = $this->createMock(Request::class);
        $request->method('getUri')->willReturnCallback(function (): Uri {
            $uri = $this->createMock(Uri::class);
            $uri->method('getBasePath')->willReturn('/subfolder');

            return $uri;
        });

        $response = new Response();
        $controller = function (): void {
            throw new UnauthorizedException();
        };

        /** @var Response $result */
        $result = $this->middleware->__invoke($request, $response, $controller);

        static::assertSame(302, $result->getStatusCode());
        static::assertSame('/subfolder/login', $result->getHeader('location')[0]);
    }

    /**
     * Test middleware execution with controller throwing a not authorized exception
     */
    public function testMiddlewareExecutionWithServerExceptionWith(): void
    {
        $request = $this->createMock(Request::class);
        $request->method('getUri')->willReturnCallback(function (): Uri {
            $uri = $this->createMock(Uri::class);
            $uri->method('getBasePath')->willReturn('/subfolder');

            return $uri;
        });

        $response = new Response();
        $controller = function (): void {
            throw new \Exception();
        };

        $parameters = [];
        $this->container->pageBuilder = $this->createMock(PageBuilder::class);
        $this->container->pageBuilder->method('render')->willReturnCallback(function (string $message): string {
            return $message;
        });
        $this->container->pageBuilder
            ->method('assign')
            ->willReturnCallback(function (string $key, string $value) use (&$parameters): void {
                $parameters[$key] = $value;
            })
        ;

        /** @var Response $result */
        $result = $this->middleware->__invoke($request, $response, $controller);

        static::assertSame(500, $result->getStatusCode());
        static::assertContains('error', (string) $result->getBody());
        static::assertSame('An unexpected error occurred.', $parameters['message']);
    }

    public function testMiddlewareExecutionWithUpdates(): void
    {
        $request = $this->createMock(Request::class);
        $request->method('getUri')->willReturnCallback(function (): Uri {
            $uri = $this->createMock(Uri::class);
            $uri->method('getBasePath')->willReturn('/subfolder');

            return $uri;
        });

        $response = new Response();
        $controller = function (Request $request, Response $response): Response {
            return $response->withStatus(418); // I'm a tea pot
        };

        $this->container->loginManager = $this->createMock(LoginManager::class);
        $this->container->loginManager->method('isLoggedIn')->willReturn(true);

        $this->container->conf = $this->createMock(ConfigManager::class);
        $this->container->conf->method('get')->willReturnCallback(function (string $key): string {
            return $key;
        });

        $this->container->pageCacheManager = $this->createMock(PageCacheManager::class);
        $this->container->pageCacheManager->expects(static::once())->method('invalidateCaches');

        $this->container->updater = $this->createMock(Updater::class);
        $this->container->updater
            ->expects(static::once())
            ->method('update')
            ->willReturn(['update123'])
        ;
        $this->container->updater->method('getDoneUpdates')->willReturn($updates = ['update123', 'other']);
        $this->container->updater
            ->expects(static::once())
            ->method('writeUpdates')
            ->with('resource.updates', $updates)
        ;

        /** @var Response $result */
        $result = $this->middleware->__invoke($request, $response, $controller);

        static::assertInstanceOf(Response::class, $result);
        static::assertSame(418, $result->getStatusCode());
    }
}
