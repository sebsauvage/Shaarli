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
     * Test displaying error with any exception (no debug): only display an error occurred with HTTP 500.
     */
    public function testDisplayAnyExceptionErrorNoDebug(): void
    {
        $request = $this->createMock(Request::class);
        $response = new Response();

        // Save RainTPL assigned variables
        $assignedVariables = [];
        $this->assignTemplateVars($assignedVariables);

        $result = ($this->controller)($request, $response, new \Exception('abc'));

        static::assertSame(500, $result->getStatusCode());
        static::assertSame('An unexpected error occurred.', $assignedVariables['message']);
        static::assertArrayNotHasKey('stacktrace', $assignedVariables);
    }
}
