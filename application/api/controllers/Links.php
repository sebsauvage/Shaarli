<?php

namespace Shaarli\Api\Controllers;

use Shaarli\Api\ApiUtils;
use Shaarli\Api\Exceptions\ApiBadParametersException;
use Shaarli\Api\Exceptions\ApiLinkNotFoundException;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class Links
 *
 * REST API Controller: all services related to links collection.
 *
 * @package Api\Controllers
 * @see http://shaarli.github.io/api-documentation/#links-links-collection
 */
class Links extends ApiController
{
    /**
     * @var int Number of links returned if no limit is provided.
     */
    public static $DEFAULT_LIMIT = 20;

    /**
     * Retrieve a list of links, allowing different filters.
     *
     * @param Request  $request  Slim request.
     * @param Response $response Slim response.
     *
     * @return Response response.
     *
     * @throws ApiBadParametersException Invalid parameters.
     */
    public function getLinks($request, $response)
    {
        $private = $request->getParam('visibility');
        $links = $this->linkDb->filterSearch(
            [
                'searchtags' => $request->getParam('searchtags', ''),
                'searchterm' => $request->getParam('searchterm', ''),
            ],
            false,
            $private
        );

        // Return links from the {offset}th link, starting from 0.
        $offset = $request->getParam('offset');
        if (! empty($offset) && ! ctype_digit($offset)) {
            throw new ApiBadParametersException('Invalid offset');
        }
        $offset = ! empty($offset) ? intval($offset) : 0;
        if ($offset > count($links)) {
            return $response->withJson([], 200, $this->jsonStyle);
        }

        // limit parameter is either a number of links or 'all' for everything.
        $limit = $request->getParam('limit');
        if (empty($limit)) {
            $limit = self::$DEFAULT_LIMIT;
        } else if (ctype_digit($limit)) {
            $limit = intval($limit);
        } else if ($limit === 'all') {
            $limit = count($links);
        } else {
            throw new ApiBadParametersException('Invalid limit');
        }

        // 'environment' is set by Slim and encapsulate $_SERVER.
        $index = index_url($this->ci['environment']);

        $out = [];
        $cpt = 0;
        foreach ($links as $link) {
            if (count($out) >= $limit) {
                break;
            }
            if ($cpt++ >= $offset) {
                $out[] = ApiUtils::formatLink($link, $index);
            }
        }

        return $response->withJson($out, 200, $this->jsonStyle);
    }

    /**
     * Return a single formatted link by its ID.
     *
     * @param Request  $request  Slim request.
     * @param Response $response Slim response.
     * @param array    $args     Path parameters. including the ID.
     *
     * @return Response containing the link array.
     *
     * @throws ApiLinkNotFoundException generating a 404 error.
     */
    public function getLink($request, $response, $args)
    {
        if (! isset($this->linkDb[$args['id']])) {
            throw new ApiLinkNotFoundException();
        }
        $index = index_url($this->ci['environment']);
        $out = ApiUtils::formatLink($this->linkDb[$args['id']], $index);
        return $response->withJson($out, 200, $this->jsonStyle);
    }
}
