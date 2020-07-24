<?php

declare(strict_types=1);

namespace Shaarli\Front\Controller\Visitor;

use PHPUnit\Framework\TestCase;
use Shaarli\Security\SessionManager;
use Slim\Http\Request;
use Slim\Http\Response;

class PublicSessionFilterControllerTest extends TestCase
{
    use FrontControllerMockHelper;

    /** @var PublicSessionFilterController */
    protected $controller;

    public function setUp(): void
    {
        $this->createContainer();

        $this->controller = new PublicSessionFilterController($this->container);
    }

    /**
     * Link per page - Default call with valid parameter and a referer.
     */
    public function testLinksPerPage(): void
    {
        $this->container->environment = ['HTTP_REFERER' => 'http://shaarli/subfolder/controller/?searchtag=abc'];

        $request = $this->createMock(Request::class);
        $request->method('getParam')->with('nb')->willReturn('8');
        $response = new Response();

        $this->container->sessionManager
            ->expects(static::once())
            ->method('setSessionParameter')
            ->with(SessionManager::KEY_LINKS_PER_PAGE, 8)
        ;

        $result = $this->controller->linksPerPage($request, $response);

        static::assertInstanceOf(Response::class, $result);
        static::assertSame(302, $result->getStatusCode());
        static::assertSame(['/subfolder/controller/?searchtag=abc'], $result->getHeader('location'));
    }

    /**
     * Link per page - Invalid value, should use default value (20)
     */
    public function testLinksPerPageNotValid(): void
    {
        $request = $this->createMock(Request::class);
        $request->method('getParam')->with('nb')->willReturn('test');
        $response = new Response();

        $this->container->sessionManager
            ->expects(static::once())
            ->method('setSessionParameter')
            ->with(SessionManager::KEY_LINKS_PER_PAGE, 20)
        ;

        $result = $this->controller->linksPerPage($request, $response);

        static::assertInstanceOf(Response::class, $result);
        static::assertSame(302, $result->getStatusCode());
        static::assertSame(['/subfolder/'], $result->getHeader('location'));
    }
}
