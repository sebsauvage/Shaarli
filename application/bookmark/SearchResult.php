<?php

declare(strict_types=1);

namespace Shaarli\Bookmark;

/**
 * Read-only class used to represent search result, including pagination.
 */
class SearchResult
{
    /** @var Bookmark[] List of result bookmarks with pagination applied */
    protected $bookmarks;

    /** @var int number of Bookmarks found, with pagination applied */
    protected $resultCount;

    /** @var int total number of result found */
    protected $totalCount;

    /** @var int pagination: limit number of result bookmarks */
    protected $limit;

    /** @var int pagination: offset to apply to complete result list */
    protected $offset;

    public function __construct(array $bookmarks, int $totalCount, int $offset, ?int $limit)
    {
        $this->bookmarks = $bookmarks;
        $this->resultCount = count($bookmarks);
        $this->totalCount = $totalCount;
        $this->limit = $limit;
        $this->offset = $offset;
    }

    /**
     * Build a SearchResult from provided full result set and pagination settings.
     *
     * @param Bookmark[] $bookmarks        Full set of result which will be filtered
     * @param int        $offset           Start recording results from $offset
     * @param int|null   $limit            End recording results after $limit bookmarks is reached
     * @param bool       $allowOutOfBounds Set to false to display the last page if the offset is out of bound,
     *                                     return empty result set otherwise (default: false)
     *
     * @return SearchResult
     */
    public static function getSearchResult(
        $bookmarks,
        int $offset = 0,
        ?int $limit = null,
        bool $allowOutOfBounds = false
    ): self {
        $totalCount = count($bookmarks);
        if (!$allowOutOfBounds && $offset > $totalCount) {
            $offset = $limit === null ? 0 : $limit * -1;
        }

        if ($bookmarks instanceof BookmarkArray) {
            $buffer = [];
            foreach ($bookmarks as $key => $value) {
                $buffer[$key] = $value;
            }
            $bookmarks = $buffer;
        }

        return new static(
            array_slice($bookmarks, $offset, $limit, true),
            $totalCount,
            $offset,
            $limit
        );
    }

    /** @return Bookmark[] List of result bookmarks with pagination applied */
    public function getBookmarks(): array
    {
        return $this->bookmarks;
    }

    /** @return int number of Bookmarks found, with pagination applied */
    public function getResultCount(): int
    {
        return $this->resultCount;
    }

    /** @return int total number of result found */
    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    /** @return int pagination: limit number of result bookmarks */
    public function getLimit(): ?int
    {
        return $this->limit;
    }

    /** @return int pagination: offset to apply to complete result list */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /** @return int Current page of result set in complete results */
    public function getPage(): int
    {
        if (empty($this->limit)) {
            return $this->offset === 0 ? 1 : 2;
        }
        $base = $this->offset >= 0 ? $this->offset : $this->totalCount + $this->offset;

        return (int) ceil($base / $this->limit) + 1;
    }

    /** @return int Get the # of the last page */
    public function getLastPage(): int
    {
        if (empty($this->limit)) {
            return $this->offset === 0 ? 1 : 2;
        }

        return (int) ceil($this->totalCount / $this->limit);
    }

    /** @return bool Either the current page is the last one or not */
    public function isLastPage(): bool
    {
        return $this->getPage() === $this->getLastPage();
    }

    /** @return bool Either the current page is the first one or not */
    public function isFirstPage(): bool
    {
        return $this->offset === 0;
    }
}
