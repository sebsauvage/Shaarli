<?php

declare(strict_types=1);

namespace Shaarli\Bookmark;

use Shaarli\TestCase;

/**
 * Test SearchResult class.
 */
class SearchResultTest extends TestCase
{
    /** Create a SearchResult without any pagination parameter. */
    public function testResultNoParameters(): void
    {
        $searchResult = SearchResult::getSearchResult($data = ['a', 'b', 'c', 'd', 'e', 'f']);

        static::assertSame($data, $searchResult->getBookmarks());
        static::assertSame(6, $searchResult->getResultCount());
        static::assertSame(6, $searchResult->getTotalCount());
        static::assertSame(null, $searchResult->getLimit());
        static::assertSame(0, $searchResult->getOffset());
        static::assertSame(1, $searchResult->getPage());
        static::assertSame(1, $searchResult->getLastPage());
        static::assertTrue($searchResult->isFirstPage());
        static::assertTrue($searchResult->isLastPage());
    }

    /** Create a SearchResult with only an offset parameter */
    public function testResultWithOffset(): void
    {
        $searchResult = SearchResult::getSearchResult(['a', 'b', 'c', 'd', 'e', 'f'], 2);

        static::assertSame([2 => 'c', 3 => 'd', 4 => 'e', 5 => 'f'], $searchResult->getBookmarks());
        static::assertSame(4, $searchResult->getResultCount());
        static::assertSame(6, $searchResult->getTotalCount());
        static::assertSame(null, $searchResult->getLimit());
        static::assertSame(2, $searchResult->getOffset());
        static::assertSame(2, $searchResult->getPage());
        static::assertSame(2, $searchResult->getLastPage());
        static::assertFalse($searchResult->isFirstPage());
        static::assertTrue($searchResult->isLastPage());
    }

    /** Create a SearchResult with only a limit parameter */
    public function testResultWithLimit(): void
    {
        $searchResult = SearchResult::getSearchResult(['a', 'b', 'c', 'd', 'e', 'f'], 0, 2);

        static::assertSame([0 => 'a', 1 => 'b'], $searchResult->getBookmarks());
        static::assertSame(2, $searchResult->getResultCount());
        static::assertSame(6, $searchResult->getTotalCount());
        static::assertSame(2, $searchResult->getLimit());
        static::assertSame(0, $searchResult->getOffset());
        static::assertSame(1, $searchResult->getPage());
        static::assertSame(3, $searchResult->getLastPage());
        static::assertTrue($searchResult->isFirstPage());
        static::assertFalse($searchResult->isLastPage());
    }

    /** Create a SearchResult with offset and limit parameters */
    public function testResultWithLimitAndOffset(): void
    {
        $searchResult = SearchResult::getSearchResult(['a', 'b', 'c', 'd', 'e', 'f'], 2, 2);

        static::assertSame([2 => 'c', 3 => 'd'], $searchResult->getBookmarks());
        static::assertSame(2, $searchResult->getResultCount());
        static::assertSame(6, $searchResult->getTotalCount());
        static::assertSame(2, $searchResult->getLimit());
        static::assertSame(2, $searchResult->getOffset());
        static::assertSame(2, $searchResult->getPage());
        static::assertSame(3, $searchResult->getLastPage());
        static::assertFalse($searchResult->isFirstPage());
        static::assertFalse($searchResult->isLastPage());
    }

    /** Create a SearchResult with offset and limit parameters displaying the last page */
    public function testResultWithLimitAndOffsetLastPage(): void
    {
        $searchResult = SearchResult::getSearchResult(['a', 'b', 'c', 'd', 'e', 'f'], 4, 2);

        static::assertSame([4 => 'e', 5 => 'f'], $searchResult->getBookmarks());
        static::assertSame(2, $searchResult->getResultCount());
        static::assertSame(6, $searchResult->getTotalCount());
        static::assertSame(2, $searchResult->getLimit());
        static::assertSame(4, $searchResult->getOffset());
        static::assertSame(3, $searchResult->getPage());
        static::assertSame(3, $searchResult->getLastPage());
        static::assertFalse($searchResult->isFirstPage());
        static::assertTrue($searchResult->isLastPage());
    }

    /** Create a SearchResult with offset and limit parameters out of bound (display the last page) */
    public function testResultWithLimitAndOffsetOutOfBounds(): void
    {
        $searchResult = SearchResult::getSearchResult(['a', 'b', 'c', 'd', 'e', 'f'], 12, 2);

        static::assertSame([4 => 'e', 5 => 'f'], $searchResult->getBookmarks());
        static::assertSame(2, $searchResult->getResultCount());
        static::assertSame(6, $searchResult->getTotalCount());
        static::assertSame(2, $searchResult->getLimit());
        static::assertSame(-2, $searchResult->getOffset());
        static::assertSame(3, $searchResult->getPage());
        static::assertSame(3, $searchResult->getLastPage());
        static::assertFalse($searchResult->isFirstPage());
        static::assertTrue($searchResult->isLastPage());
    }

    /** Create a SearchResult with offset and limit parameters out of bound (no result) */
    public function testResultWithLimitAndOffsetOutOfBoundsNoResult(): void
    {
        $searchResult = SearchResult::getSearchResult(['a', 'b', 'c', 'd', 'e', 'f'], 12, 2, true);

        static::assertSame([], $searchResult->getBookmarks());
        static::assertSame(0, $searchResult->getResultCount());
        static::assertSame(6, $searchResult->getTotalCount());
        static::assertSame(2, $searchResult->getLimit());
        static::assertSame(12, $searchResult->getOffset());
        static::assertSame(7, $searchResult->getPage());
        static::assertSame(3, $searchResult->getLastPage());
        static::assertFalse($searchResult->isFirstPage());
        static::assertFalse($searchResult->isLastPage());
    }
}
