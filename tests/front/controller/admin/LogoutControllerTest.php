<?php

declare(strict_types=1);

namespace Shaarli\Front\Controller\Admin;

use Shaarli\Security\CookieManager;
use Shaarli\Security\SessionManager;
use Shaarli\TestCase;
use Slim\Http\Request;
use Slim\Http\Response;

class LogoutControllerTest extends TestCase
{
    use FrontAdminControllerMockHelper;

    /** @var LogoutController */
    protected $controller;

    public function setUp(): void
    {
        $this->createContainer();

        $this->controller = new LogoutController($this->container);
    }

    public function testValidControllerInvoke(): void
    {
        $request = $this->createMock(Request::class);
        $response = new Response();

        $this->container->pageCacheManager->expects(static::once())->method('invalidateCaches');

        $this->container->sessionManager = $this->createMock(SessionManager::class);
        $this->container->sessionManager->expects(static::once())->method('logout');

        $this->container->cookieManager = $this->createMock(CookieManager::class);
        $this->container->cookieManager
            ->expects(static::once())
            ->method('setCookieParameter')
            ->with(CookieManager::STAY_SIGNED_IN, 'false', 0, '/subfolder/')
        ;

        $result = $this->controller->index($request, $response);

        static::assertInstanceOf(Response::class, $result);
        static::assertSame(302, $result->getStatusCode());
        static::assertSame(['/subfolder/'], $result->getHeader('location'));
    }
}
