<?php

declare(strict_types=1);

namespace Shaarli\Front\Controller\Visitor;

use Shaarli\TestCase;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\Uri;

class ErrorNotFoundControllerTest extends TestCase
{
    use FrontControllerMockHelper;

    /** @var ErrorNotFoundController */
    protected $controller;

    public function setUp(): void
    {
        $this->createContainer();

        $this->controller = new ErrorNotFoundController($this->container);
    }

    /**
     * Test displaying 404 error
     */
    public function testDisplayNotFoundError(): void
    {
        $request = $this->createMock(Request::class);
        $request->expects(static::once())->method('getRequestTarget')->willReturn('/');
        $request->method('getUri')->willReturnCallback(function (): Uri {
            $uri = $this->createMock(Uri::class);
            $uri->method('getBasePath')->willReturn('/subfolder');

            return $uri;
        });

        $response = new Response();

        // Save RainTPL assigned variables
        $assignedVariables = [];
        $this->assignTemplateVars($assignedVariables);

        $result = ($this->controller)(
            $request,
            $response
        );

        static::assertSame(404, $result->getStatusCode());
        static::assertSame('404', (string) $result->getBody());
        static::assertSame('Requested page could not be found.', $assignedVariables['error_message']);
    }

    /**
     * Test displaying 404 error from REST API
     */
    public function testDisplayNotFoundErrorFromAPI(): void
    {
        $request = $this->createMock(Request::class);
        $request->expects(static::once())->method('getRequestTarget')->willReturn('/sufolder/api/v1/links');
        $request->method('getUri')->willReturnCallback(function (): Uri {
            $uri = $this->createMock(Uri::class);
            $uri->method('getBasePath')->willReturn('/subfolder');

            return $uri;
        });

        $response = new Response();

        // Save RainTPL assigned variables
        $assignedVariables = [];
        $this->assignTemplateVars($assignedVariables);

        $result = ($this->controller)($request, $response);

        static::assertSame(404, $result->getStatusCode());
        static::assertSame([], $assignedVariables);
    }
}
