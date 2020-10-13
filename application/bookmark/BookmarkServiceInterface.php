<?php

declare(strict_types=1);

namespace Shaarli\Bookmark;

use Shaarli\Bookmark\Exception\BookmarkNotFoundException;
use Shaarli\Bookmark\Exception\NotWritableDataStoreException;

/**
 * Class BookmarksService
 *
 * This is the entry point to manipulate the bookmark DB.
 *
 * Regarding return types of a list of bookmarks, it can either be an array or an ArrayAccess implementation,
 * so until PHP 8.0 is the minimal supported version with union return types it cannot be explicitly added.
 */
interface BookmarkServiceInterface
{
    /**
     * Find a bookmark by hash
     *
     * @param string $hash
     *
     * @return Bookmark
     *
     * @throws \Exception
     */
    public function findByHash(string $hash): Bookmark;

    /**
     * @param $url
     *
     * @return Bookmark|null
     */
    public function findByUrl(string $url): ?Bookmark;

    /**
     * Search bookmarks
     *
     * @param array   $request
     * @param ?string $visibility
     * @param bool    $caseSensitive
     * @param bool    $untaggedOnly
     * @param bool    $ignoreSticky
     *
     * @return Bookmark[]
     */
    public function search(
        array $request = [],
        string $visibility = null,
        bool $caseSensitive = false,
        bool $untaggedOnly = false,
        bool $ignoreSticky = false
    );

    /**
     * Get a single bookmark by its ID.
     *
     * @param int    $id          Bookmark ID
     * @param ?string $visibility all|public|private e.g. with public, accessing a private bookmark will throw an
     *                            exception
     *
     * @return Bookmark
     *
     * @throws BookmarkNotFoundException
     * @throws \Exception
     */
    public function get(int $id, string $visibility = null);

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
    public function set(Bookmark $bookmark, bool $save = true): Bookmark;

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
    public function add(Bookmark $bookmark, bool $save = true): Bookmark;

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
    public function addOrSet(Bookmark $bookmark, bool $save = true): Bookmark;

    /**
     * Deletes a bookmark.
     *
     * @param Bookmark $bookmark
     * @param bool     $save
     *
     * @throws \Exception
     */
    public function remove(Bookmark $bookmark, bool $save = true): void;

    /**
     * Get a single bookmark by its ID.
     *
     * @param int     $id         Bookmark ID
     * @param ?string $visibility all|public|private e.g. with public, accessing a private bookmark will throw an
     *                            exception
     *
     * @return bool
     */
    public function exists(int $id, string $visibility = null): bool;

    /**
     * Return the number of available bookmarks for given visibility.
     *
     * @param ?string $visibility public|private|all
     *
     * @return int Number of bookmarks
     */
    public function count(string $visibility = null): int;

    /**
     * Write the datastore.
     *
     * @throws NotWritableDataStoreException
     */
    public function save(): void;

    /**
     * Returns the list tags appearing in the bookmarks with the given tags
     *
     * @param array|null  $filteringTags tags selecting the bookmarks to consider
     * @param string|null $visibility    process only all/private/public bookmarks
     *
     * @return array tag => bookmarksCount
     */
    public function bookmarksCountPerTag(array $filteringTags = [], ?string $visibility = null): array;

    /**
     * Returns the list of days containing articles (oldest first)
     *
     * @return array containing days (in format YYYYMMDD).
     */
    public function days(): array;

    /**
     * Returns the list of articles for a given day.
     *
     * @param string $request day to filter. Format: YYYYMMDD.
     *
     * @return Bookmark[] list of shaare found.
     *
     * @throws BookmarkNotFoundException
     */
    public function filterDay(string $request);

    /**
     * Creates the default database after a fresh install.
     */
    public function initialize(): void;
}
