<?php

namespace Shaarli\Bookmark\Exception;

use Shaarli\Bookmark\Bookmark;

class InvalidBookmarkException extends \Exception
{
    public function __construct($bookmark)
    {
        if ($bookmark instanceof Bookmark) {
            if ($bookmark->getCreated() instanceof \DateTime) {
                $created = $bookmark->getCreated()->format(\DateTime::ATOM);
            } elseif (empty($bookmark->getCreated())) {
                $created = '';
            } else {
                $created = 'Not a DateTime object';
            }
            $this->message = 'This bookmark is not valid' . PHP_EOL;
            $this->message .= ' - ID: ' . $bookmark->getId() . PHP_EOL;
            $this->message .= ' - Title: ' . $bookmark->getTitle() . PHP_EOL;
            $this->message .= ' - Url: ' . $bookmark->getUrl() . PHP_EOL;
            $this->message .= ' - ShortUrl: ' . $bookmark->getShortUrl() . PHP_EOL;
            $this->message .= ' - Created: ' . $created . PHP_EOL;
        } else {
            $this->message = 'The provided data is not a bookmark' . PHP_EOL;
            $this->message .= var_export($bookmark, true);
        }
    }
}
