<?php

namespace Shaarli\Tests\Utils;

use Shaarli\Bookmark\BookmarkFileService;

class FakeBookmarkService extends BookmarkFileService
{
    public function getBookmarks()
    {
        return $this->bookmarks;
    }
}
