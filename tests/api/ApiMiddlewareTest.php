<?php
namespace Shaarli\Api;

use Shaarli\Config\ConfigManager;
use Shaarli\History;
use Slim\Container;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class ApiMiddlewareTest
 *
 * Test the REST API Slim Middleware.
 *
 * Note that we can't test a valid use case here, because the middleware
 * needs to call a valid controller/action during its execution.
 *
 * @package Api
 */
class ApiMiddlewareTest extends \Shaarli\TestCase
{
    /**
     * @var string datastore to test write operations
     */
    protected static $testDatastore = 'sandbox/datastore.php';

    /**
     * @var ConfigManager instance
     */
    protected $conf;

    /**
     * @var \ReferenceLinkDB instance.
     */
    protected $refDB = null;

    /**
     * @var Container instance.
     */
    protected $container;

    /**
     * Before every test, instantiate a new Api with its config, plugins and bookmarks.
     */
    protected function setUp(): void
    {
        $this->conf = new ConfigManager('tests/utils/config/configJson');
        $this->conf->set('api.secret', 'NapoleonWasALizard');

        $this->refDB = new \ReferenceLinkDB();
        $this->refDB->write(self::$testDatastore);

        $history = new History('sandbox/history.php');

        $this->container = new Container();
        $this->container['conf'] = $this->conf;
        $this->container['history'] = $history;
    }

    /**
     * After every test, remove the test datastore.
     */
    protected function tearDown(): void
    {
        @unlink(self::$testDatastore);
    }

    /**
     * Invoke the middleware with a valid token
     */
    public function testInvokeMiddlewareWithValidToken(): void
    {
        $next = function (Request $request, Response $response): Response {
            return $response;
        };
        $mw = new ApiMiddleware($this->container);
        $env = Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/echo',
            'HTTP_AUTHORIZATION'=> 'Bearer ' . ApiUtilsTest::generateValidJwtToken('NapoleonWasALizard'),
        ]);
        $request = Request::createFromEnvironment($env);
        $response = new Response();
        /** @var Response $response */
        $response = $mw($request, $response, $next);

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Invoke the middleware with a valid token
     * Using specific Apache CGI redirected authorization.
     */
    public function testInvokeMiddlewareWithValidTokenFromRedirectedHeader(): void
    {
        $next = function (Request $request, Response $response): Response {
            return $response;
        };

        $token = 'Bearer ' . ApiUtilsTest::generateValidJwtToken('NapoleonWasALizard');
        $this->container->environment['REDIRECT_HTTP_AUTHORIZATION'] = $token;
        $mw = new ApiMiddleware($this->container);
        $env = Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/echo',
        ]);
        $request = Request::createFromEnvironment($env);
        $response = new Response();
        /** @var Response $response */
        $response = $mw($request, $response, $next);

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Invoke the middleware with the API disabled:
     * should return a 401 error Unauthorized.
     */
    public function testInvokeMiddlewareApiDisabled()
    {
        $this->conf->set('api.enabled', false);
        $mw = new ApiMiddleware($this->container);
        $env = Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/echo',
        ]);
        $request = Request::createFromEnvironment($env);
        $response = new Response();
        /** @var Response $response */
        $response = $mw($request, $response, null);

        $this->assertEquals(401, $response->getStatusCode());
        $body = json_decode((string) $response->getBody());
        $this->assertEquals('Not authorized', $body);
    }

    /**
     * Invoke the middleware with the API disabled in debug mode:
     * should return a 401 error Unauthorized - with a specific message and a stacktrace.
     */
    public function testInvokeMiddlewareApiDisabledDebug()
    {
        $this->conf->set('api.enabled', false);
        $this->conf->set('dev.debug', true);
        $mw = new ApiMiddleware($this->container);
        $env = Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/echo',
        ]);
        $request = Request::createFromEnvironment($env);
        $response = new Response();
        /** @var Response $response */
        $response = $mw($request, $response, null);

        $this->assertEquals(401, $response->getStatusCode());
        $body = json_decode((string) $response->getBody());
        $this->assertEquals('Not authorized: API is disabled', $body->message);
        $this->assertContainsPolyfill('ApiAuthorizationException', $body->stacktrace);
    }

    /**
     * Invoke the middleware without a token (debug):
     * should return a 401 error Unauthorized - with a specific message and a stacktrace.
     */
    public function testInvokeMiddlewareNoTokenProvidedDebug()
    {
        $this->conf->set('dev.debug', true);
        $mw = new ApiMiddleware($this->container);
        $env = Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/echo',
        ]);
        $request = Request::createFromEnvironment($env);
        $response = new Response();
        /** @var Response $response */
        $response = $mw($request, $response, null);

        $this->assertEquals(401, $response->getStatusCode());
        $body = json_decode((string) $response->getBody());
        $this->assertEquals('Not authorized: JWT token not provided', $body->message);
        $this->assertContainsPolyfill('ApiAuthorizationException', $body->stacktrace);
    }

    /**
     * Invoke the middleware without a secret set in settings (debug):
     * should return a 401 error Unauthorized - with a specific message and a stacktrace.
     */
    public function testInvokeMiddlewareNoSecretSetDebug()
    {
        $this->conf->set('dev.debug', true);
        $this->conf->set('api.secret', '');
        $mw = new ApiMiddleware($this->container);
        $env = Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/echo',
            'HTTP_AUTHORIZATION'=> 'Bearer jwt',
        ]);
        $request = Request::createFromEnvironment($env);
        $response = new Response();
        /** @var Response $response */
        $response = $mw($request, $response, null);

        $this->assertEquals(401, $response->getStatusCode());
        $body = json_decode((string) $response->getBody());
        $this->assertEquals('Not authorized: Token secret must be set in Shaarli\'s administration', $body->message);
        $this->assertContainsPolyfill('ApiAuthorizationException', $body->stacktrace);
    }

    /**
     * Invoke the middleware with an invalid JWT token header
     */
    public function testInvalidJwtAuthHeaderDebug()
    {
        $this->conf->set('dev.debug', true);
        $mw = new ApiMiddleware($this->container);
        $env = Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/echo',
            'HTTP_AUTHORIZATION'=> 'PolarBearer jwt',
        ]);
        $request = Request::createFromEnvironment($env);
        $response = new Response();
        /** @var Response $response */
        $response = $mw($request, $response, null);

        $this->assertEquals(401, $response->getStatusCode());
        $body = json_decode((string) $response->getBody());
        $this->assertEquals('Not authorized: Invalid JWT header', $body->message);
        $this->assertContainsPolyfill('ApiAuthorizationException', $body->stacktrace);
    }

    /**
     * Invoke the middleware with an invalid JWT token (debug):
     * should return a 401 error Unauthorized - with a specific message and a stacktrace.
     *
     * Note: specific JWT errors tests are handled in ApiUtilsTest.
     */
    public function testInvokeMiddlewareInvalidJwtDebug()
    {
        $this->conf->set('dev.debug', true);
        $mw = new ApiMiddleware($this->container);
        $env = Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/echo',
            'HTTP_AUTHORIZATION'=> 'Bearer jwt',
        ]);
        $request = Request::createFromEnvironment($env);
        $response = new Response();
        /** @var Response $response */
        $response = $mw($request, $response, null);

        $this->assertEquals(401, $response->getStatusCode());
        $body = json_decode((string) $response->getBody());
        $this->assertEquals('Not authorized: Malformed JWT token', $body->message);
        $this->assertContainsPolyfill('ApiAuthorizationException', $body->stacktrace);
    }
}
