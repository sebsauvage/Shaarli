<?php

namespace Shaarli\Formatter;

/**
 * Class BookmarkDefaultFormatter
 *
 * Default bookmark formatter.
 * Escape values for HTML display and automatically add link to URL and hashtags.
 *
 * @package Shaarli\Formatter
 */
class BookmarkDefaultFormatter extends BookmarkFormatter
{
    /**
     * @inheritdoc
     */
    public function formatTitle($bookmark)
    {
        return escape($bookmark->getTitle());
    }

    /**
     * @inheritdoc
     */
    public function formatDescription($bookmark)
    {
        $indexUrl = ! empty($this->contextData['index_url']) ? $this->contextData['index_url'] : '';
        return format_description(escape($bookmark->getDescription()), $indexUrl);
    }

    /**
     * @inheritdoc
     */
    protected function formatTagList($bookmark)
    {
        return escape(parent::formatTagList($bookmark));
    }

    /**
     * @inheritdoc
     */
    public function formatTagString($bookmark)
    {
        return implode(' ', $this->formatTagList($bookmark));
    }

    /**
     * @inheritdoc
     */
    public function formatUrl($bookmark)
    {
        if (! empty($this->contextData['index_url']) && (
            startsWith($bookmark->getUrl(), '?') || startsWith($bookmark->getUrl(), '/')
        )) {
            return $this->contextData['index_url'] . escape($bookmark->getUrl());
        }
        return escape($bookmark->getUrl());
    }

    /**
     * @inheritdoc
     */
    protected function formatRealUrl($bookmark)
    {
        if (! empty($this->contextData['index_url']) && (
                startsWith($bookmark->getUrl(), '?') || startsWith($bookmark->getUrl(), '/')
            )) {
            return $this->contextData['index_url'] . escape($bookmark->getUrl());
        }
        return escape($bookmark->getUrl());
    }

    /**
     * @inheritdoc
     */
    protected function formatThumbnail($bookmark)
    {
        return escape($bookmark->getThumbnail());
    }
}
