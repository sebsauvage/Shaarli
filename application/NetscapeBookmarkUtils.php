<?php

/**
 * Utilities to import and export bookmarks using the Netscape format
 */
class NetscapeBookmarkUtils
{

    /**
     * Filters links and adds Netscape-formatted fields
     *
     * Added fields:
     * - timestamp  link addition date, using the Unix epoch format
     * - taglist    comma-separated tag list
     *
     * @param LinkDB $linkDb         Link datastore
     * @param string $selection      Which links to export: (all|private|public)
     * @param bool   $prependNoteUrl Prepend note permalinks with the server's URL
     * @param string $indexUrl       Absolute URL of the Shaarli index page
     *
     * @throws Exception Invalid export selection
     *
     * @return array The links to be exported, with additional fields
     */
    public static function filterAndFormat($linkDb, $selection, $prependNoteUrl, $indexUrl)
    {
        // see tpl/export.html for possible values
        if (! in_array($selection, array('all', 'public', 'private'))) {
            throw new Exception('Invalid export selection: "'.$selection.'"');
        }

        $bookmarkLinks = array();

        foreach ($linkDb as $link) {
            if ($link['private'] != 0 && $selection == 'public') {
                continue;
            }
            if ($link['private'] == 0 && $selection == 'private') {
                continue;
            }
            $date = DateTime::createFromFormat(LinkDB::LINK_DATE_FORMAT, $link['linkdate']);
            $link['timestamp'] = $date->getTimestamp();
            $link['taglist'] = str_replace(' ', ',', $link['tags']);

            if (startsWith($link['url'], '?') && $prependNoteUrl) {
                $link['url'] = $indexUrl . $link['url'];
            }

            $bookmarkLinks[] = $link;
        }

        return $bookmarkLinks;
    }
}
