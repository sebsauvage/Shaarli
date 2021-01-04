<?php

declare(strict_types=1);

namespace Shaarli\Front\Controller\Admin;

use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Controller used to retrieve/update bookmark's metadata.
 */
class MetadataController extends ShaarliAdminController
{
    /**
     * GET /admin/metadata/{url} - Attempt to retrieve the bookmark title from provided URL.
     */
    public function ajaxRetrieveTitle(Request $request, Response $response): Response
    {
        $url = $request->getParam('url');

        // Only try to extract metadata from URL with HTTP(s) scheme
        if (!empty($url) && strpos(get_url_scheme($url) ?: '', 'http') !== false) {
            return $response->withJson($this->container->metadataRetriever->retrieve($url));
        }

        return $response->withJson([]);
    }
}
