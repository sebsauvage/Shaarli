<?php

use WebThumbnailer\WebThumbnailer;

/**
 * Class Thumbnailer
 *
 * Utility class used to retrieve thumbnails using web-thumbnailer dependency.
 */
class Thumbnailer
{
    /**
     * @var WebThumbnailer instance.
     */
    protected $wt;

    /**
     * @var ConfigManager instance.
     */
    protected $conf;

    /**
     * Thumbnailer constructor.
     *
     * @param ConfigManager $conf instance.
     */
    public function __construct($conf)
    {
        $this->conf = $conf;
        $this->wt = new WebThumbnailer();
        \WebThumbnailer\Application\ConfigManager::addFile('inc/web-thumbnailer.json');
        $this->wt->maxWidth($this->conf->get('thumbnails.width'))
                 ->maxHeight($this->conf->get('thumbnails.height'))
                 ->crop(true)
                 ->debug($this->conf->get('dev.debug', false));
    }

    /**
     * Retrieve a thumbnail for given URL
     *
     * @param string $url where to look for a thumbnail.
     *
     * @return bool|string The thumbnail relative cache file path, or false if none has been found.
     */
    public function get($url)
    {
        return $this->wt->thumbnail($url);
    }
}
