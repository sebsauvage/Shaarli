<?php

declare(strict_types=1);

namespace Shaarli\Front\Controller\Admin;

use Shaarli\Security\CookieManager;
use Shaarli\Security\LoginManager;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class LogoutController
 *
 * Slim controller used to logout the user.
 * It invalidates page cache and terminate the user session. Then it redirects to the homepage.
 */
class LogoutController extends ShaarliAdminController
{
    public function index(Request $request, Response $response): Response
    {
        $this->container->pageCacheManager->invalidateCaches();
        $this->container->sessionManager->logout();
        $this->container->cookieManager->setCookieParameter(
            CookieManager::STAY_SIGNED_IN,
            'false',
            0,
            $this->container->basePath . '/'
        );

        return $this->redirect($response, '/');
    }
}
