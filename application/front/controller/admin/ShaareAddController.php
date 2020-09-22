<?php

declare(strict_types=1);

namespace Shaarli\Front\Controller\Admin;

use Shaarli\Formatter\BookmarkMarkdownFormatter;
use Shaarli\Render\TemplatePage;
use Slim\Http\Request;
use Slim\Http\Response;

class ShaareAddController extends ShaarliAdminController
{
    /**
     * GET /admin/add-shaare - Displays the form used to create a new bookmark from an URL
     */
    public function addShaare(Request $request, Response $response): Response
    {
        $tags = $this->container->bookmarkService->bookmarksCountPerTag();
        if ($this->container->conf->get('formatter') === 'markdown') {
            $tags[BookmarkMarkdownFormatter::NO_MD_TAG] = 1;
        }

        $this->assignView(
            'pagetitle',
            t('Shaare a new link') . ' - ' . $this->container->conf->get('general.title', 'Shaarli')
        );
        $this->assignView('tags', $tags);
        $this->assignView('default_private_links', $this->container->conf->get('privacy.default_private_links', false));
        $this->assignView('async_metadata', $this->container->conf->get('general.enable_async_metadata', true));

        return $response->write($this->render(TemplatePage::ADDLINK));
    }
}
