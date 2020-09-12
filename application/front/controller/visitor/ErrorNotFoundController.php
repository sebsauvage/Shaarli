<?php

declare(strict_types=1);

namespace Shaarli\Front\Controller\Visitor;

use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Controller used to render the 404 error page.
 */
class ErrorNotFoundController extends ShaarliVisitorController
{
    public function __invoke(Request $request, Response $response): Response
    {
        // Request from the API
        if (false !== strpos($request->getRequestTarget(), '/api/v1')) {
            return $response->withStatus(404);
        }

        // This is required because the middleware is ignored if the route is not found.
        $this->container->basePath = rtrim($request->getUri()->getBasePath(), '/');

        $this->assignView('error_message', t('Requested page could not be found.'));

        return $response->withStatus(404)->write($this->render('404'));
    }
}
