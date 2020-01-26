<?php

declare(strict_types=1);

namespace Shaarli\Front\Controller;

use PHPUnit\Framework\TestCase;
use Shaarli\Bookmark\BookmarkServiceInterface;
use Shaarli\Config\ConfigManager;
use Shaarli\Container\ShaarliContainer;
use Shaarli\Front\Exception\LoginBannedException;
use Shaarli\Plugin\PluginManager;
use Shaarli\Render\PageBuilder;
use Shaarli\Security\LoginManager;
use Slim\Http\Request;
use Slim\Http\Response;

class LoginControllerTest extends TestCase
{
    /** @var ShaarliContainer */
    protected $container;

    /** @var LoginController */
    protected $controller;

    public function setUp(): void
    {
        $this->container = $this->createMock(ShaarliContainer::class);
        $this->controller = new LoginController($this->container);
    }

    public function testValidControllerInvoke(): void
    {
        $this->createValidContainerMockSet();

        $request = $this->createMock(Request::class);
        $request->expects(static::once())->method('getServerParam')->willReturn('> referer');
        $response = new Response();

        $assignedVariables = [];
        $this->container->pageBuilder
            ->method('assign')
            ->willReturnCallback(function ($key, $value) use (&$assignedVariables) {
                $assignedVariables[$key] = $value;

                return $this;
            })
        ;

        $result = $this->controller->index($request, $response);

        static::assertInstanceOf(Response::class, $result);
        static::assertSame(200, $result->getStatusCode());
        static::assertSame('loginform', (string) $result->getBody());

        static::assertSame('&gt; referer', $assignedVariables['returnurl']);
        static::assertSame(true, $assignedVariables['remember_user_default']);
        static::assertSame('Login - Shaarli', $assignedVariables['pagetitle']);
    }

    public function testValidControllerInvokeWithUserName(): void
    {
        $this->createValidContainerMockSet();

        $request = $this->createMock(Request::class);
        $request->expects(static::once())->method('getServerParam')->willReturn('> referer');
        $request->expects(static::exactly(2))->method('getParam')->willReturn('myUser>');
        $response = new Response();

        $assignedVariables = [];
        $this->container->pageBuilder
            ->method('assign')
            ->willReturnCallback(function ($key, $value) use (&$assignedVariables) {
                $assignedVariables[$key] = $value;

                return $this;
            })
        ;

        $result = $this->controller->index($request, $response);

        static::assertInstanceOf(Response::class, $result);
        static::assertSame(200, $result->getStatusCode());
        static::assertSame('loginform', (string) $result->getBody());

        static::assertSame('myUser&gt;', $assignedVariables['username']);
        static::assertSame('&gt; referer', $assignedVariables['returnurl']);
        static::assertSame(true, $assignedVariables['remember_user_default']);
        static::assertSame('Login - Shaarli', $assignedVariables['pagetitle']);
    }

    public function testLoginControllerWhileLoggedIn(): void
    {
        $request = $this->createMock(Request::class);
        $response = new Response();

        $loginManager = $this->createMock(LoginManager::class);
        $loginManager->expects(static::once())->method('isLoggedIn')->willReturn(true);
        $this->container->loginManager = $loginManager;

        $result = $this->controller->index($request, $response);

        static::assertInstanceOf(Response::class, $result);
        static::assertSame(302, $result->getStatusCode());
        static::assertSame(['./'], $result->getHeader('Location'));
    }

    public function testLoginControllerOpenShaarli(): void
    {
        $this->createValidContainerMockSet();

        $request = $this->createMock(Request::class);
        $response = new Response();

        $conf = $this->createMock(ConfigManager::class);
        $conf->method('get')->willReturnCallback(function (string $parameter, $default) {
            if ($parameter === 'security.open_shaarli') {
                return true;
            }
            return $default;
        });
        $this->container->conf = $conf;

        $result = $this->controller->index($request, $response);

        static::assertInstanceOf(Response::class, $result);
        static::assertSame(302, $result->getStatusCode());
        static::assertSame(['./'], $result->getHeader('Location'));
    }

    public function testLoginControllerWhileBanned(): void
    {
        $this->createValidContainerMockSet();

        $request = $this->createMock(Request::class);
        $response = new Response();

        $loginManager = $this->createMock(LoginManager::class);
        $loginManager->method('isLoggedIn')->willReturn(false);
        $loginManager->method('canLogin')->willReturn(false);
        $this->container->loginManager = $loginManager;

        $this->expectException(LoginBannedException::class);

        $this->controller->index($request, $response);
    }

    protected function createValidContainerMockSet(): void
    {
        // User logged out
        $loginManager = $this->createMock(LoginManager::class);
        $loginManager->method('isLoggedIn')->willReturn(false);
        $loginManager->method('canLogin')->willReturn(true);
        $this->container->loginManager = $loginManager;

        // Config
        $conf = $this->createMock(ConfigManager::class);
        $conf->method('get')->willReturnCallback(function (string $parameter, $default) {
            return $default;
        });
        $this->container->conf = $conf;

        // PageBuilder
        $pageBuilder = $this->createMock(PageBuilder::class);
        $pageBuilder
            ->method('render')
            ->willReturnCallback(function (string $template): string {
                return $template;
            })
        ;
        $this->container->pageBuilder = $pageBuilder;

        $pluginManager = $this->createMock(PluginManager::class);
        $this->container->pluginManager = $pluginManager;
        $bookmarkService = $this->createMock(BookmarkServiceInterface::class);
        $this->container->bookmarkService = $bookmarkService;
    }
}
