<?php

declare(strict_types=1);

namespace Shaarli\Feed;

use DatePeriod;

/**
 * Simple cache system, mainly for the RSS/ATOM feeds
 */
class CachedPage
{
    /** Directory containing page caches */
    protected $cacheDir;

    /** Should this URL be cached (boolean)? */
    protected $shouldBeCached;

    /** Name of the cache file for this URL */
    protected $filename;

    /** @var DatePeriod|null Optionally specify a period of time for cache validity */
    protected $validityPeriod;

    /**
     * Creates a new CachedPage
     *
     * @param string      $cacheDir       page cache directory
     * @param string      $url            page URL
     * @param bool        $shouldBeCached whether this page needs to be cached
     * @param ?DatePeriod $validityPeriod Optionally specify a time limit on requested cache
     */
    public function __construct($cacheDir, $url, $shouldBeCached, ?DatePeriod $validityPeriod)
    {
        // TODO: check write access to the cache directory
        $this->cacheDir = $cacheDir;
        $this->filename = $this->cacheDir . '/' . sha1($url) . '.cache';
        $this->shouldBeCached = $shouldBeCached;
        $this->validityPeriod = $validityPeriod;
    }

    /**
     * Returns the cached version of a page, if it exists and should be cached
     *
     * @return string a cached version of the page if it exists, null otherwise
     */
    public function cachedVersion()
    {
        if (!$this->shouldBeCached) {
            return null;
        }
        if (!is_file($this->filename)) {
            return null;
        }
        if ($this->validityPeriod !== null) {
            $cacheDate = \DateTime::createFromFormat('U', (string) filemtime($this->filename));
            if (
                $cacheDate < $this->validityPeriod->getStartDate()
                || $cacheDate > $this->validityPeriod->getEndDate()
            ) {
                return null;
            }
        }

        return file_get_contents($this->filename);
    }

    /**
     * Puts a page in the cache
     *
     * @param string $pageContent XML content to cache
     */
    public function cache($pageContent)
    {
        if (!$this->shouldBeCached) {
            return;
        }
        file_put_contents($this->filename, $pageContent);
    }
}
