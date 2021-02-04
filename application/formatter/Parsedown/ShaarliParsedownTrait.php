<?php

declare(strict_types=1);

namespace Shaarli\Formatter\Parsedown;

use Shaarli\Formatter\BookmarkDefaultFormatter as Formatter;

/**
 * Trait used for Parsedown and ParsedownExtra extension.
 *
 * Extended:
 *   - Format links properly in search context
 */
trait ShaarliParsedownTrait
{
    /**
     * @inheritDoc
     */
    protected function inlineLink($excerpt)
    {
        return $this->shaarliFormatLink(parent::inlineLink($excerpt), true);
    }

    /**
     * @inheritDoc
     */
    protected function inlineUrl($excerpt)
    {
        return $this->shaarliFormatLink(parent::inlineUrl($excerpt), false);
    }

    /**
     * Properly format markdown link:
     *   - remove highlight tags from HREF attribute
     *   - (optional) add highlight tags to link caption
     *
     * @param array|null $link     Parsedown formatted link array.
     *                             It can be empty.
     * @param bool       $fullWrap Add highlight tags the whole link caption
     *
     * @return array|null
     */
    protected function shaarliFormatLink(?array $link, bool $fullWrap): ?array
    {
        // If open and clean search tokens are found in the link, process.
        if (
            is_array($link)
            && strpos($link['element']['attributes']['href'] ?? '', Formatter::SEARCH_HIGHLIGHT_OPEN) !== false
            && strpos($link['element']['attributes']['href'] ?? '', Formatter::SEARCH_HIGHLIGHT_CLOSE) !== false
        ) {
            $link['element']['attributes']['href'] = $this->shaarliRemoveSearchTokens(
                $link['element']['attributes']['href']
            );

            if ($fullWrap) {
                $link['element']['text'] = Formatter::SEARCH_HIGHLIGHT_OPEN .
                    $link['element']['text'] .
                    Formatter::SEARCH_HIGHLIGHT_CLOSE
                ;
            }
        }

        return $link;
    }

    /**
     * Remove open and close tags from provided string.
     *
     * @param string $entry input
     *
     * @return string Striped input
     */
    protected function shaarliRemoveSearchTokens(string $entry): string
    {
        $entry = str_replace(Formatter::SEARCH_HIGHLIGHT_OPEN, '', $entry);
        $entry = str_replace(Formatter::SEARCH_HIGHLIGHT_CLOSE, '', $entry);

        return $entry;
    }
}
