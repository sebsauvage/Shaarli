<?php

namespace Shaarli\Bookmark;


use Shaarli\Bookmark\Exception\BookmarkNotFoundException;
use Shaarli\Bookmark\Exception\NotWritableDataStoreException;
use Shaarli\Config\ConfigManager;
use Shaarli\History;

/**
 * Class BookmarksService
 *
 * This is the entry point to manipulate the bookmark DB.
 */
interface BookmarkServiceInterface
{
    /**
     * BookmarksService constructor.
     *
     * @param ConfigManager $conf       instance
     * @param History       $history    instance
     * @param bool          $isLoggedIn true if the current user is logged in
     */
    public function __construct(ConfigManager $conf, History $history, $isLoggedIn);

    /**
     * Find a bookmark by hash
     *
     * @param string $hash
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function findByHash($hash);

    /**
     * @param $url
     *
     * @return Bookmark|null
     */
    public function findByUrl($url);

    /**
     * Search bookmarks
     *
     * @param mixed  $request
     * @param string $visibility
     * @param bool   $caseSensitive
     * @param bool   $untaggedOnly
     * @param bool   $ignoreSticky
     *
     * @return Bookmark[]
     */
    public function search(
        $request = [],
        $visibility = null,
        $caseSensitive = false,
        $untaggedOnly = false,
        bool $ignoreSticky = false
    );

    /**
     * Get a single bookmark by its ID.
     *
     * @param int    $id         Bookmark ID
     * @param string $visibility all|public|private e.g. with public, accessing a private bookmark will throw an
     *                           exception
     *
     * @return Bookmark
     *
     * @throws BookmarkNotFoundException
     * @throws \Exception
     */
    public function get($id, $visibility = null);

    /**
     * Updates an existing bookmark (depending on its ID).
     *
     * @param Bookmark $bookmark
     * @param bool     $save Writes to the datastore if set to true
     *
     * @return Bookmark Updated bookmark
     *
     * @throws BookmarkNotFoundException
     * @throws \Exception
     */
    public function set($bookmark, $save = true);

    /**
     * Adds a new bookmark (the ID must be empty).
     *
     * @param Bookmark $bookmark
     * @param bool     $save Writes to the datastore if set to true
     *
     * @return Bookmark new bookmark
     *
     * @throws \Exception
     */
    public function add($bookmark, $save = true);

    /**
     * Adds or updates a bookmark depending on its ID:
     *   - a Bookmark without ID will be added
     *   - a Bookmark with an existing ID will be updated
     *
     * @param Bookmark $bookmark
     * @param bool     $save
     *
     * @return Bookmark
     *
     * @throws \Exception
     */
    public function addOrSet($bookmark, $save = true);

    /**
     * Deletes a bookmark.
     *
     * @param Bookmark $bookmark
     * @param bool     $save
     *
     * @throws \Exception
     */
    public function remove($bookmark, $save = true);

    /**
     * Get a single bookmark by its ID.
     *
     * @param int    $id         Bookmark ID
     * @param string $visibility all|public|private e.g. with public, accessing a private bookmark will throw an
     *                           exception
     *
     * @return bool
     */
    public function exists($id, $visibility = null);

    /**
     * Return the number of available bookmarks for given visibility.
     *
     * @param string $visibility public|private|all
     *
     * @return int Number of bookmarks
     */
    public function count($visibility = null);

    /**
     * Write the datastore.
     *
     * @throws NotWritableDataStoreException
     */
    public function save();

    /**
     * Returns the list tags appearing in the bookmarks with the given tags
     *
     * @param array  $filteringTags tags selecting the bookmarks to consider
     * @param string $visibility    process only all/private/public bookmarks
     *
     * @return array tag => bookmarksCount
     */
    public function bookmarksCountPerTag($filteringTags = [], $visibility = 'all');

    /**
     * Returns the list of days containing articles (oldest first)
     *
     * @return array containing days (in format YYYYMMDD).
     */
    public function days();

    /**
     * Returns the list of articles for a given day.
     *
     * @param string $request day to filter. Format: YYYYMMDD.
     *
     * @return Bookmark[] list of shaare found.
     *
     * @throws BookmarkNotFoundException
     */
    public function filterDay($request);

    /**
     * Creates the default database after a fresh install.
     */
    public function initialize();
}
