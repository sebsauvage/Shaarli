<?php

declare(strict_types=1);

namespace Shaarli\Front\Controller\Visitor;

use Shaarli\Front\Exception\ShaarliFrontException;
use Shaarli\TestCase;
use Slim\Http\Request;
use Slim\Http\Response;

class ErrorControllerTest extends TestCase
{
    use FrontControllerMockHelper;

    /** @var ErrorController */
    protected $controller;

    public function setUp(): void
    {
        $this->createContainer();

        $this->controller = new ErrorController($this->container);
    }

    /**
     * Test displaying error with a ShaarliFrontException: display exception message and use its code for HTTTP code
     */
    public function testDisplayFrontExceptionError(): void
    {
        $request = $this->createMock(Request::class);
        $response = new Response();

        $message = 'error message';
        $errorCode = 418;

        // Save RainTPL assigned variables
        $assignedVariables = [];
        $this->assignTemplateVars($assignedVariables);

        $result = ($this->controller)(
            $request,
            $response,
            new class($message, $errorCode) extends ShaarliFrontException {}
        );

        static::assertSame($errorCode, $result->getStatusCode());
        static::assertSame($message, $assignedVariables['message']);
        static::assertArrayNotHasKey('stacktrace', $assignedVariables);
    }

    /**
     * Test displaying error with any exception (no debug) while logged in:
     * display full error details
     */
    public function testDisplayAnyExceptionErrorNoDebugLoggedIn(): void
    {
        $request = $this->createMock(Request::class);
        $response = new Response();

        // Save RainTPL assigned variables
        $assignedVariables = [];
        $this->assignTemplateVars($assignedVariables);

        $this->container->loginManager->method('isLoggedIn')->willReturn(true);

        $result = ($this->controller)($request, $response, new \Exception('abc'));

        static::assertSame(500, $result->getStatusCode());
        static::assertSame('Error: abc', $assignedVariables['message']);
        static::assertContainsPolyfill('Please report it on Github', $assignedVariables['text']);
        static::assertArrayHasKey('stacktrace', $assignedVariables);
    }

    /**
     * Test displaying error with any exception (no debug) while logged out:
     * display standard error without detail
     */
    public function testDisplayAnyExceptionErrorNoDebug(): void
    {
        $request = $this->createMock(Request::class);
        $response = new Response();

        // Save RainTPL assigned variables
        $assignedVariables = [];
        $this->assignTemplateVars($assignedVariables);

        $this->container->loginManager->method('isLoggedIn')->willReturn(false);

        $result = ($this->controller)($request, $response, new \Exception('abc'));

        static::assertSame(500, $result->getStatusCode());
        static::assertSame('An unexpected error occurred.', $assignedVariables['message']);
        static::assertArrayNotHasKey('text', $assignedVariables);
        static::assertArrayNotHasKey('stacktrace', $assignedVariables);
    }
}
