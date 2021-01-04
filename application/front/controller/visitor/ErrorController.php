<?php

declare(strict_types=1);

namespace Shaarli\Front\Controller\Visitor;

use Shaarli\Front\Exception\ShaarliFrontException;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Controller used to render the error page, with a provided exception.
 * It is actually used as a Slim error handler.
 */
class ErrorController extends ShaarliVisitorController
{
    public function __invoke(Request $request, Response $response, \Throwable $throwable): Response
    {
        // Unknown error encountered
        $this->container->pageBuilder->reset();

        if ($throwable instanceof ShaarliFrontException) {
            // Functional error
            $this->assignView('message', nl2br($throwable->getMessage()));

            $response = $response->withStatus($throwable->getCode());
        } else {
            // Internal error (any other Throwable)
            if ($this->container->conf->get('dev.debug', false) || $this->container->loginManager->isLoggedIn()) {
                $this->assignView('message', t('Error: ') . $throwable->getMessage());
                $this->assignView(
                    'text',
                    '<a href="https://github.com/shaarli/Shaarli/issues/new">'
                    . t('Please report it on Github.')
                    . '</a>'
                );
                $this->assignView('stacktrace', exception2text($throwable));
            } else {
                $this->assignView('message', t('An unexpected error occurred.'));
            }

            $response = $response->withStatus(500);
        }

        return $response->write($this->render('error'));
    }
}
