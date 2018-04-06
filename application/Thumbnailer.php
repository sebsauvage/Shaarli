<?php

namespace Shaarli;

use Shaarli\Config\ConfigManager;
use WebThumbnailer\Exception\WebThumbnailerException;
use WebThumbnailer\WebThumbnailer;
use WebThumbnailer\Application\ConfigManager as WTConfigManager;

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

        if (! $this->checkRequirements()) {
            $this->conf->set('thumbnails.enabled', false);
            $this->conf->write(true);
            // TODO: create a proper error handling system able to catch exceptions...
            die(t('php-gd extension must be loaded to use thumbnails. Thumbnails are now disabled. Please reload the page.'));
        }

        $this->wt = new WebThumbnailer();
        WTConfigManager::addFile('inc/web-thumbnailer.json');
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
        try {
            return $this->wt->thumbnail($url);
        } catch (WebThumbnailerException $e) {
            // Exceptions are only thrown in debug mode.
            error_log(get_class($e) .': '. $e->getMessage());
            return false;
        }
    }

    /**
     * Make sure that requirements are match to use thumbnails:
     *   - php-gd is loaded
     */
    protected function checkRequirements()
    {
        return extension_loaded('gd');
    }
}
