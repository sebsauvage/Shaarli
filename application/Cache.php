<?php
/**
 * Cache utilities
 */

/**
 * Purges all cached pages
 *
 * @param string $pageCacheDir page cache directory
 */
function purgeCachedPages($pageCacheDir)
{
    if (! is_dir($pageCacheDir)) {
        return;
    }

    // TODO: check write access to the cache directory

    $handler = opendir($pageCacheDir);
    if ($handler == false) {
        return;
    }

    while (($filename = readdir($handler)) !== false) {
        if (endsWith($filename, '.cache')) {
                unlink($pageCacheDir.'/'.$filename);
        }
    }
    closedir($handler);
}

/**
 * Invalidates caches when the database is changed or the user logs out.
 *
 * @param string $pageCacheDir page cache directory
 */
function invalidateCaches($pageCacheDir)
{
    // Purge cache attached to session.
    if (isset($_SESSION['tags'])) {
        unset($_SESSION['tags']);
    }

    // Purge page cache shared by sessions.
    purgeCachedPages($pageCacheDir);
}
