<?php

namespace Shaarli\Front;

use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Middleware used for controller requiring to be authenticated.
 * It extends ShaarliMiddleware, and just make sure that the user is authenticated.
 * Otherwise, it redirects to the login page.
 */
class ShaarliAdminMiddleware extends ShaarliMiddleware
{
    public function __invoke(Request $request, Response $response, callable $next): Response
    {
        $this->initBasePath($request);

        if (true !== $this->container->loginManager->isLoggedIn()) {
            $returnUrl = urlencode($this->container->environment['REQUEST_URI']);

            return $response->withRedirect($this->container->basePath . '/login?returnurl=' . $returnUrl);
        }

        return parent::__invoke($request, $response, $next);
    }
}
