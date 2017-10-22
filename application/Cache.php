<?php
/**
 * Cache utilities
 */

/**
 * Purges all cached pages
 *
 * @param string $pageCacheDir page cache directory
 *
 * @return mixed an error string if the directory is missing
 */
function purgeCachedPages($pageCacheDir)
{
    if (! is_dir($pageCacheDir)) {
        $error = sprintf(t('Cannot purge %s: no directory'), $pageCacheDir);
        error_log($error);
        return $error;
    }

    array_map('unlink', glob($pageCacheDir.'/*.cache'));
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
