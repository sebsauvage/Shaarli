<?php

declare(strict_types=1);

namespace Shaarli\Legacy;

use Shaarli\Feed\FeedBuilder;
use Shaarli\Front\Controller\Visitor\ShaarliVisitorController;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * We use this to maintain legacy routes, and redirect requests to the corresponding Slim route.
 * Only public routes, and both `?addlink` and `?post` were kept here.
 * Other routes will just display the linklist.
 *
 * @deprecated
 */
class LegacyController extends ShaarliVisitorController
{
    /** @var string[] Both `?post` and `?addlink` do not use `?do=` format. */
    public const LEGACY_GET_ROUTES = [
        'post',
        'addlink',
    ];

    /**
     * This method will call `$action` method, which will redirect to corresponding Slim route.
     */
    public function process(Request $request, Response $response, string $action): Response
    {
        if (!method_exists($this, $action)) {
            throw new UnknowLegacyRouteException();
        }

        return $this->{$action}($request, $response);
    }

    /** Legacy route: ?post= */
    public function post(Request $request, Response $response): Response
    {
        $parameters = count($request->getQueryParams()) > 0 ? '?' . http_build_query($request->getQueryParams()) : '';

        if (!$this->container->loginManager->isLoggedIn()) {
            return $this->redirect($response, '/login?returnurl=/admin/shaare' . $parameters);
        }

        return $this->redirect($response, '/admin/shaare' . $parameters);
    }

    /** Legacy route: ?addlink= */
    protected function addlink(Request $request, Response $response): Response
    {
        if (!$this->container->loginManager->isLoggedIn()) {
            return $this->redirect($response, '/login?returnurl=/admin/add-shaare');
        }

        return $this->redirect($response, '/admin/add-shaare');
    }

    /** Legacy route: ?do=login */
    protected function login(Request $request, Response $response): Response
    {
        $returnurl = $request->getQueryParam('returnurl');

        return $this->redirect($response, '/login' . ($returnurl ? '?returnurl=' . $returnurl : ''));
    }

    /** Legacy route: ?do=logout */
    protected function logout(Request $request, Response $response): Response
    {
        return $this->redirect($response, '/admin/logout');
    }

    /** Legacy route: ?do=picwall */
    protected function picwall(Request $request, Response $response): Response
    {
        return $this->redirect($response, '/picture-wall');
    }

    /** Legacy route: ?do=tagcloud */
    protected function tagcloud(Request $request, Response $response): Response
    {
        return $this->redirect($response, '/tags/cloud');
    }

    /** Legacy route: ?do=taglist */
    protected function taglist(Request $request, Response $response): Response
    {
        return $this->redirect($response, '/tags/list');
    }

    /** Legacy route: ?do=daily */
    protected function daily(Request $request, Response $response): Response
    {
        $dayParam = !empty($request->getParam('day')) ? '?day=' . escape($request->getParam('day')) : '';

        return $this->redirect($response, '/daily' . $dayParam);
    }

    /** Legacy route: ?do=rss */
    protected function rss(Request $request, Response $response): Response
    {
        return $this->feed($request, $response, FeedBuilder::$FEED_RSS);
    }

    /** Legacy route: ?do=atom */
    protected function atom(Request $request, Response $response): Response
    {
        return $this->feed($request, $response, FeedBuilder::$FEED_ATOM);
    }

    /** Legacy route: ?do=opensearch */
    protected function opensearch(Request $request, Response $response): Response
    {
        return $this->redirect($response, '/open-search');
    }

    /** Legacy route: ?do=dailyrss */
    protected function dailyrss(Request $request, Response $response): Response
    {
        return $this->redirect($response, '/daily-rss');
    }

    /** Legacy route: ?do=feed */
    protected function feed(Request $request, Response $response, string $feedType): Response
    {
        $parameters = count($request->getQueryParams()) > 0 ? '?' . http_build_query($request->getQueryParams()) : '';

        return $this->redirect($response, '/feed/' . $feedType . $parameters);
    }
}
