<?php

declare(strict_types=1);

namespace Shaarli\Formatter\Parsedown;

use Shaarli\Formatter\BookmarkDefaultFormatter as Formatter;

trait ShaarliParsedownTrait
{
    protected function inlineLink($excerpt)
    {
        return $this->shaarliFormatLink(parent::inlineLink($excerpt), true);
    }

    protected function inlineUrl($excerpt)
    {
        return $this->shaarliFormatLink(parent::inlineUrl($excerpt), false);
    }

    protected function shaarliFormatLink(?array $link, bool $fullWrap): ?array
    {
        if (
            is_array($link)
            && strpos($link['element']['attributes']['href'], Formatter::SEARCH_HIGHLIGHT_OPEN) !== false
            && strpos($link['element']['attributes']['href'], Formatter::SEARCH_HIGHLIGHT_CLOSE) !== false
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

    protected function shaarliRemoveSearchTokens(string $entry): string
    {
        $entry = str_replace(Formatter::SEARCH_HIGHLIGHT_OPEN, '', $entry);
        $entry = str_replace(Formatter::SEARCH_HIGHLIGHT_CLOSE, '', $entry);

        return $entry;
    }
}
