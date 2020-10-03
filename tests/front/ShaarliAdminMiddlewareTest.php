<?php

declare(strict_types=1);

namespace Shaarli\Front;

use Shaarli\Config\ConfigManager;
use Shaarli\Container\ShaarliContainer;
use Shaarli\Security\LoginManager;
use Shaarli\TestCase;
use Shaarli\Updater\Updater;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\Uri;

class ShaarliAdminMiddlewareTest extends TestCase
{
    protected const TMP_MOCK_FILE = '.tmp';

    /** @var ShaarliContainer */
    protected $container;

    /** @var ShaarliMiddleware  */
    protected $middleware;

    public function setUp(): void
    {
        $this->container = $this->createMock(ShaarliContainer::class);

        touch(static::TMP_MOCK_FILE);

        $this->container->conf = $this->createMock(ConfigManager::class);
        $this->container->conf->method('getConfigFileExt')->willReturn(static::TMP_MOCK_FILE);

        $this->container->loginManager = $this->createMock(LoginManager::class);
        $this->container->updater = $this->createMock(Updater::class);

        $this->container->environment = ['REQUEST_URI' => 'http://shaarli/subfolder/path'];

        $this->middleware = new ShaarliAdminMiddleware($this->container);
    }

    public function tearDown(): void
    {
        unlink(static::TMP_MOCK_FILE);
    }

    /**
     * Try to access an admin controller while logged out -> redirected to login page.
     */
    public function testMiddlewareWhileLoggedOut(): void
    {
        $this->container->loginManager->expects(static::once())->method('isLoggedIn')->willReturn(false);

        $request = $this->createMock(Request::class);
        $request->method('getUri')->willReturnCallback(function (): Uri {
            $uri = $this->createMock(Uri::class);
            $uri->method('getBasePath')->willReturn('/subfolder');

            return $uri;
        });

        $response = new Response();

        /** @var Response $result */
        $result = $this->middleware->__invoke($request, $response, function () {});

        static::assertSame(302, $result->getStatusCode());
        static::assertSame(
            '/subfolder/login?returnurl=' . urlencode('http://shaarli/subfolder/path'),
            $result->getHeader('location')[0]
        );
    }

    /**
     * Process controller while logged in.
     */
    public function testMiddlewareWhileLoggedIn(): void
    {
        $this->container->loginManager->method('isLoggedIn')->willReturn(true);

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

        static::assertSame(418, $result->getStatusCode());
    }
}
