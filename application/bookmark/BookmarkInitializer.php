<?php

namespace Shaarli\Bookmark;

/**
 * Class BookmarkInitializer
 *
 * This class is used to initialized default bookmarks after a fresh install of Shaarli.
 * It is no longer call when the data store is empty,
 * because user might want to delete default bookmarks after the install.
 *
 * To prevent data corruption, it does not overwrite existing bookmarks,
 * even though there should not be any.
 *
 * @package Shaarli\Bookmark
 */
class BookmarkInitializer
{
    /** @var BookmarkServiceInterface */
    protected $bookmarkService;

    /**
     * BookmarkInitializer constructor.
     *
     * @param BookmarkServiceInterface $bookmarkService
     */
    public function __construct($bookmarkService)
    {
        $this->bookmarkService = $bookmarkService;
    }

    /**
     * Initialize the data store with default bookmarks
     */
    public function initialize()
    {
        $this->bookmarkService->enableAnonymousPermission();

        $bookmark = new Bookmark();
        $bookmark->setTitle(t('My secret stuff... - Pastebin.com'));
        $bookmark->setUrl('http://sebsauvage.net/paste/?8434b27936c09649#bR7XsXhoTiLcqCpQbmOpBi3rq2zzQUC5hBI7ZT1O3x8=');
        $bookmark->setDescription(t('Shhhh! I\'m a private link only YOU can see. You can delete me too.'));
        $bookmark->setTagsString('secretstuff');
        $bookmark->setPrivate(true);
        $this->bookmarkService->add($bookmark, false);

        $bookmark = new Bookmark();
        $bookmark->setTitle(t('The personal, minimalist, super-fast, database free, bookmarking service'));
        $bookmark->setUrl('https://shaarli.readthedocs.io', []);
        $bookmark->setDescription(t(
            'Welcome to Shaarli! This is your first public bookmark. '
            . 'To edit or delete me, you must first login.

To learn how to use Shaarli, consult the link "Documentation" at the bottom of this page.

You use the community supported version of the original Shaarli project, by Sebastien Sauvage.'
        ));
        $bookmark->setTagsString('opensource software');
        $this->bookmarkService->add($bookmark, false);

        $this->bookmarkService->save();

        $this->bookmarkService->disableAnonymousPermission();
    }
}
