<?php

use Shaarli\Bookmark\BookmarkArray;
use Shaarli\Bookmark\BookmarkFilter;
use Shaarli\Bookmark\BookmarkIO;
use Shaarli\Bookmark\BookmarkFileService;
use Shaarli\Bookmark\Exception\EmptyDataStoreException;
use Shaarli\Config\ConfigManager;
use Shaarli\History;

class FakeBookmarkService extends BookmarkFileService
{
    public function getBookmarks()
    {
        return $this->bookmarks;
    }
}
