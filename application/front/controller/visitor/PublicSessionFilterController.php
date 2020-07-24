<?php

declare(strict_types=1);

namespace Shaarli\Front\Controller\Visitor;

use Shaarli\Security\SessionManager;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Slim controller used to handle filters stored in the visitor session, links per page, etc.
 */
class PublicSessionFilterController extends ShaarliVisitorController
{
    /**
     * GET /links-per-page: set the number of bookmarks to display per page in homepage
     */
    public function linksPerPage(Request $request, Response $response): Response
    {
        $linksPerPage = $request->getParam('nb') ?? null;
        if (null === $linksPerPage || false === is_numeric($linksPerPage)) {
            $linksPerPage = $this->container->conf->get('general.links_per_page', 20);
        }

        $this->container->sessionManager->setSessionParameter(
            SessionManager::KEY_LINKS_PER_PAGE,
            abs(intval($linksPerPage))
        );

        return $this->redirectFromReferer($request, $response, ['linksperpage'], ['nb']);
    }
}
