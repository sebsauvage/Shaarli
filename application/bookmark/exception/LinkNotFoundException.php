<?php
namespace Shaarli\Bookmark\Exception;

use Exception;

class LinkNotFoundException extends Exception
{
    /**
     * LinkNotFoundException constructor.
     */
    public function __construct()
    {
        $this->message = t('The link you are trying to reach does not exist or has been deleted.');
    }
}
