<?php

namespace Shaarli\Formatter;

use Shaarli\Config\ConfigManager;
use Shaarli\Formatter\Parsedown\ShaarliParsedownExtra;

/**
 * Class BookmarkMarkdownExtraFormatter
 *
 * Format bookmark description into MarkdownExtra format.
 *
 * @see https://michelf.ca/projects/php-markdown/extra/
 *
 * @package Shaarli\Formatter
 */
class BookmarkMarkdownExtraFormatter extends BookmarkMarkdownFormatter
{
    public function __construct(ConfigManager $conf, bool $isLoggedIn)
    {
        parent::__construct($conf, $isLoggedIn);
        $this->parsedown = new ShaarliParsedownExtra();
    }
}
