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
        } elseif (ctype_digit($limit)) {
            $limit = intval($limit);
        } elseif ($limit === 'all') {
            $limit = count($links);
        } else {
            throw new ApiBadParametersException('Invalid limit');
        }

        // 'environment' is set by Slim and encapsulate $_SERVER.
        $indexUrl = index_url($this->ci['environment']);

        $out = [];
        $index = 0;
        foreach ($links as $link) {
            if (count($out) >= $limit) {
                break;
            }
            if ($index++ >= $offset) {
                $out[] = ApiUtils::formatLink($link, $indexUrl);
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
        if (!isset($this->linkDb[$args['id']])) {
            throw new ApiLinkNotFoundException();
        }
        $index = index_url($this->ci['environment']);
        $out = ApiUtils::formatLink($this->linkDb[$args['id']], $index);

        return $response->withJson($out, 200, $this->jsonStyle);
    }

    /**
     * Creates a new link from posted request body.
     *
     * @param Request  $request  Slim request.
     * @param Response $response Slim response.
     *
     * @return Response response.
     */
    public function postLink($request, $response)
    {
        $data = $request->getParsedBody();
        $link = ApiUtils::buildLinkFromRequest($data, $this->conf->get('privacy.default_private_links'));
        // duplicate by URL, return 409 Conflict
        if (! empty($link['url']) && ! empty($dup = $this->linkDb->getLinkFromUrl($link['url']))) {
            return $response->withJson(
                ApiUtils::formatLink($dup, index_url($this->ci['environment'])),
                409,
                $this->jsonStyle
            );
        }

        $link['id'] = $this->linkDb->getNextId();
        $link['shorturl'] = link_small_hash($link['created'], $link['id']);

        // note: general relative URL
        if (empty($link['url'])) {
            $link['url'] = '?' . $link['shorturl'];
        }

        if (empty($link['title'])) {
            $link['title'] = $link['url'];
        }

        $this->linkDb[$link['id']] = $link;
        $this->linkDb->save($this->conf->get('resource.page_cache'));
        $this->history->addLink($link);
        $out = ApiUtils::formatLink($link, index_url($this->ci['environment']));
        $redirect = $this->ci->router->relativePathFor('getLink', ['id' => $link['id']]);
        return $response->withAddedHeader('Location', $redirect)
                        ->withJson($out, 201, $this->jsonStyle);
    }

    /**
     * Updates an existing link from posted request body.
     *
     * @param Request  $request  Slim request.
     * @param Response $response Slim response.
     * @param array    $args     Path parameters. including the ID.
     *
     * @return Response response.
     *
     * @throws ApiLinkNotFoundException generating a 404 error.
     */
    public function putLink($request, $response, $args)
    {
        if (! isset($this->linkDb[$args['id']])) {
            throw new ApiLinkNotFoundException();
        }

        $index = index_url($this->ci['environment']);
        $data = $request->getParsedBody();

        $requestLink = ApiUtils::buildLinkFromRequest($data, $this->conf->get('privacy.default_private_links'));
        // duplicate URL on a different link, return 409 Conflict
        if (! empty($requestLink['url'])
            && ! empty($dup = $this->linkDb->getLinkFromUrl($requestLink['url']))
            && $dup['id'] != $args['id']
        ) {
            return $response->withJson(
                ApiUtils::formatLink($dup, $index),
                409,
                $this->jsonStyle
            );
        }

        $responseLink = $this->linkDb[$args['id']];
        $responseLink = ApiUtils::updateLink($responseLink, $requestLink);
        $this->linkDb[$responseLink['id']] = $responseLink;
        $this->linkDb->save($this->conf->get('resource.page_cache'));
        $this->history->updateLink($responseLink);

        $out = ApiUtils::formatLink($responseLink, $index);
        return $response->withJson($out, 200, $this->jsonStyle);
    }

    /**
     * Delete an existing link by its ID.
     *
     * @param Request  $request  Slim request.
     * @param Response $response Slim response.
     * @param array    $args     Path parameters. including the ID.
     *
     * @return Response response.
     *
     * @throws ApiLinkNotFoundException generating a 404 error.
     */
    public function deleteLink($request, $response, $args)
    {
        if (! isset($this->linkDb[$args['id']])) {
            throw new ApiLinkNotFoundException();
        }
        $link = $this->linkDb[$args['id']];
        unset($this->linkDb[(int) $args['id']]);
        $this->linkDb->save($this->conf->get('resource.page_cache'));
        $this->history->deleteLink($link);

        return $response->withStatus(204);
    }
}
