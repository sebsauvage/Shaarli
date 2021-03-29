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
 * REST API Controller: all services related to bookmarks collection.
 *
 * @package Api\Controllers
 * @see http://shaarli.github.io/api-documentation/#links-links-collection
 */
class Links extends ApiController
{
    /**
     * @var int Number of bookmarks returned if no limit is provided.
     */
    public static $DEFAULT_LIMIT = 20;

    /**
     * Retrieve a list of bookmarks, allowing different filters.
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

        // Return bookmarks from the {offset}th link, starting from 0.
        $offset = $request->getParam('offset');
        if (! empty($offset) && ! ctype_digit($offset)) {
            throw new ApiBadParametersException('Invalid offset');
        }
        $offset = ! empty($offset) ? intval($offset) : 0;

        // limit parameter is either a number of bookmarks or 'all' for everything.
        $limit = $request->getParam('limit');
        if (empty($limit)) {
            $limit = self::$DEFAULT_LIMIT;
        } elseif (ctype_digit($limit)) {
            $limit = intval($limit);
        } elseif ($limit === 'all') {
            $limit = null;
        } else {
            throw new ApiBadParametersException('Invalid limit');
        }

        $searchResult = $this->bookmarkService->search(
            [
                'searchtags' => $request->getParam('searchtags', ''),
                'searchterm' => $request->getParam('searchterm', ''),
            ],
            $private,
            false,
            false,
            false,
            [
                'limit' => $limit,
                'offset' => $offset,
                'allowOutOfBounds' => true,
            ]
        );

        // 'environment' is set by Slim and encapsulate $_SERVER.
        $indexUrl = index_url($this->ci['environment']);

        $out = [];
        foreach ($searchResult->getBookmarks() as $bookmark) {
            $out[] = ApiUtils::formatLink($bookmark, $indexUrl);
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
        $id = is_integer_mixed($args['id']) ? (int) $args['id'] : null;
        if ($id === null || ! $this->bookmarkService->exists($id)) {
            throw new ApiLinkNotFoundException();
        }
        $index = index_url($this->ci['environment']);
        $out = ApiUtils::formatLink($this->bookmarkService->get($id), $index);

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
        $data = (array) ($request->getParsedBody() ?? []);
        $bookmark = ApiUtils::buildBookmarkFromRequest(
            $data,
            $this->conf->get('privacy.default_private_links'),
            $this->conf->get('general.tags_separator', ' ')
        );
        // duplicate by URL, return 409 Conflict
        if (
            ! empty($bookmark->getUrl())
            && ! empty($dup = $this->bookmarkService->findByUrl($bookmark->getUrl()))
        ) {
            return $response->withJson(
                ApiUtils::formatLink($dup, index_url($this->ci['environment'])),
                409,
                $this->jsonStyle
            );
        }

        $this->bookmarkService->add($bookmark);
        $out = ApiUtils::formatLink($bookmark, index_url($this->ci['environment']));
        $redirect = $this->ci->router->pathFor('getLink', ['id' => $bookmark->getId()]);
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
        $id = is_integer_mixed($args['id']) ? (int) $args['id'] : null;
        if ($id === null || !$this->bookmarkService->exists($id)) {
            throw new ApiLinkNotFoundException();
        }

        $index = index_url($this->ci['environment']);
        $data = $request->getParsedBody();

        $requestBookmark = ApiUtils::buildBookmarkFromRequest(
            $data,
            $this->conf->get('privacy.default_private_links'),
            $this->conf->get('general.tags_separator', ' ')
        );
        // duplicate URL on a different link, return 409 Conflict
        if (
            ! empty($requestBookmark->getUrl())
            && ! empty($dup = $this->bookmarkService->findByUrl($requestBookmark->getUrl()))
            && $dup->getId() != $id
        ) {
            return $response->withJson(
                ApiUtils::formatLink($dup, $index),
                409,
                $this->jsonStyle
            );
        }

        $responseBookmark = $this->bookmarkService->get($id);
        $responseBookmark = ApiUtils::updateLink($responseBookmark, $requestBookmark);
        $this->bookmarkService->set($responseBookmark);

        $out = ApiUtils::formatLink($responseBookmark, $index);
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
        $id = is_integer_mixed($args['id']) ? (int) $args['id'] : null;
        if ($id === null || !$this->bookmarkService->exists($id)) {
            throw new ApiLinkNotFoundException();
        }
        $bookmark = $this->bookmarkService->get($id);
        $this->bookmarkService->remove($bookmark);

        return $response->withStatus(204);
    }
}
