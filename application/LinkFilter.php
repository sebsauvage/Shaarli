<?php

/**
 * Class LinkFilter.
 *
 * Perform search and filter operation on link data list.
 */
class LinkFilter
{
    /**
     * @var string permalinks.
     */
    public static $FILTER_HASH   = 'permalink';

    /**
     * @var string text search.
     */
    public static $FILTER_TEXT   = 'fulltext';

    /**
     * @var string tag filter.
     */
    public static $FILTER_TAG    = 'tags';

    /**
     * @var string filter by day.
     */
    public static $FILTER_DAY    = 'FILTER_DAY';

    /**
     * @var array all available links.
     */
    private $links;

    /**
     * @param array $links initialization.
     */
    public function __construct($links)
    {
        $this->links = $links;
    }

    /**
     * Filter links according to parameters.
     *
     * @param string $type          Type of filter (eg. tags, permalink, etc.).
     * @param mixed  $request       Filter content.
     * @param bool   $casesensitive Optional: Perform case sensitive filter if true.
     * @param bool   $privateonly   Optional: Only returns private links if true.
     *
     * @return array filtered link list.
     */
    public function filter($type, $request, $casesensitive = false, $privateonly = false)
    {
        switch($type) {
            case self::$FILTER_HASH:
                return $this->filterSmallHash($request);
            case self::$FILTER_TAG | self::$FILTER_TEXT:
                if (!empty($request)) {
                    $filtered = $this->links;
                    if (isset($request[0])) {
                        $filtered = $this->filterTags($request[0], $casesensitive, $privateonly);
                    }
                    if (isset($request[1])) {
                        $lf = new LinkFilter($filtered);
                        $filtered = $lf->filterFulltext($request[1], $privateonly);
                    }
                    return $filtered;
                }
                return $this->noFilter($privateonly);
            case self::$FILTER_TEXT:
                return $this->filterFulltext($request, $privateonly);
            case self::$FILTER_TAG:
                return $this->filterTags($request, $casesensitive, $privateonly);
            case self::$FILTER_DAY:
                return $this->filterDay($request);
            default:
                return $this->noFilter($privateonly);
        }
    }

    /**
     * Unknown filter, but handle private only.
     *
     * @param bool $privateonly returns private link only if true.
     *
     * @return array filtered links.
     */
    private function noFilter($privateonly = false)
    {
        if (! $privateonly) {
            krsort($this->links);
            return $this->links;
        }

        $out = array();
        foreach ($this->links as $value) {
            if ($value['private']) {
                $out[$value['linkdate']] = $value;
            }
        }

        krsort($out);
        return $out;
    }

    /**
     * Returns the shaare corresponding to a smallHash.
     *
     * @param string $smallHash permalink hash.
     *
     * @return array $filtered array containing permalink data.
     *
     * @throws LinkNotFoundException if the smallhash doesn't match any link.
     */
    private function filterSmallHash($smallHash)
    {
        $filtered = array();
        foreach ($this->links as $l) {
            if ($smallHash == smallHash($l['linkdate'])) {
                // Yes, this is ugly and slow
                $filtered[$l['linkdate']] = $l;
                return $filtered;
            }
        }

        if (empty($filtered)) {
            throw new LinkNotFoundException();
        }

        return $filtered;
    }

    /**
     * Returns the list of links corresponding to a full-text search
     *
     * Searches:
     *  - in the URLs, title and description;
     *  - are case-insensitive;
     *  - terms surrounded by quotes " are exact terms search.
     *  - terms starting with a dash - are excluded (except exact terms).
     *
     * Example:
     *    print_r($mydb->filterFulltext('hollandais'));
     *
     * mb_convert_case($val, MB_CASE_LOWER, 'UTF-8')
     *  - allows to perform searches on Unicode text
     *  - see https://github.com/shaarli/Shaarli/issues/75 for examples
     *
     * @param string $searchterms search query.
     * @param bool   $privateonly return only private links if true.
     *
     * @return array search results.
     */
    private function filterFulltext($searchterms, $privateonly = false)
    {
        if (empty($searchterms)) {
            return $this->links;
        }

        $filtered = array();
        $search = mb_convert_case(html_entity_decode($searchterms), MB_CASE_LOWER, 'UTF-8');
        $exactRegex = '/"([^"]+)"/';
        // Retrieve exact search terms.
        preg_match_all($exactRegex, $search, $exactSearch);
        $exactSearch = array_values(array_filter($exactSearch[1]));

        // Remove exact search terms to get AND terms search.
        $explodedSearchAnd = explode(' ', trim(preg_replace($exactRegex, '', $search)));
        $explodedSearchAnd = array_values(array_filter($explodedSearchAnd));

        // Filter excluding terms and update andSearch.
        $excludeSearch = array();
        $andSearch = array();
        foreach ($explodedSearchAnd as $needle) {
            if ($needle[0] == '-' && strlen($needle) > 1) {
                $excludeSearch[] = substr($needle, 1);
            } else {
                $andSearch[] = $needle;
            }
        }

        $keys = array('title', 'description', 'url', 'tags');

        // Iterate over every stored link.
        foreach ($this->links as $link) {

            // ignore non private links when 'privatonly' is on.
            if (! $link['private'] && $privateonly === true) {
                continue;
            }

            // Concatenate link fields to search across fields.
            // Adds a '\' separator for exact search terms.
            $content = '';
            foreach ($keys as $key) {
                $content .= mb_convert_case($link[$key], MB_CASE_LOWER, 'UTF-8') . '\\';
            }

            // Be optimistic
            $found = true;

            // First, we look for exact term search
            for ($i = 0; $i < count($exactSearch) && $found; $i++) {
                $found = strpos($content, $exactSearch[$i]) !== false;
            }

            // Iterate over keywords, if keyword is not found,
            // no need to check for the others. We want all or nothing.
            for ($i = 0; $i < count($andSearch) && $found; $i++) {
                $found = strpos($content, $andSearch[$i]) !== false;
            }

            // Exclude terms.
            for ($i = 0; $i < count($excludeSearch) && $found; $i++) {
                $found = strpos($content, $excludeSearch[$i]) === false;
            }

            if ($found) {
                $filtered[$link['linkdate']] = $link;
            }
        }

        krsort($filtered);
        return $filtered;
    }

    /**
     * Returns the list of links associated with a given list of tags
     *
     * You can specify one or more tags, separated by space or a comma, e.g.
     *  print_r($mydb->filterTags('linux programming'));
     *
     * @param string $tags          list of tags separated by commas or blank spaces.
     * @param bool   $casesensitive ignore case if false.
     * @param bool   $privateonly   returns private links only.
     *
     * @return array filtered links.
     */
    public function filterTags($tags, $casesensitive = false, $privateonly = false)
    {
        // Implode if array for clean up.
        $tags = is_array($tags) ? trim(implode(' ', $tags)) : $tags;
        if (empty($tags)) {
            return $this->links;
        }

        $searchtags = self::tagsStrToArray($tags, $casesensitive);
        $filtered = array();
        if (empty($searchtags)) {
            return $filtered;
        }

        foreach ($this->links as $link) {
            // ignore non private links when 'privatonly' is on.
            if (! $link['private'] && $privateonly === true) {
                continue;
            }

            $linktags = self::tagsStrToArray($link['tags'], $casesensitive);

            $found = true;
            for ($i = 0 ; $i < count($searchtags) && $found; $i++) {
                // Exclusive search, quit if tag found.
                // Or, tag not found in the link, quit.
                if (($searchtags[$i][0] == '-' && in_array(substr($searchtags[$i], 1), $linktags))
                    || ($searchtags[$i][0] != '-') && ! in_array($searchtags[$i], $linktags)
                ) {
                    $found = false;
                }
            }

            if ($found) {
                $filtered[$link['linkdate']] = $link;
            }
        }
        krsort($filtered);
        return $filtered;
    }

    /**
     * Returns the list of articles for a given day, chronologically sorted
     *
     * Day must be in the form 'YYYYMMDD' (e.g. '20120125'), e.g.
     *  print_r($mydb->filterDay('20120125'));
     *
     * @param string $day day to filter.
     *
     * @return array all link matching given day.
     *
     * @throws Exception if date format is invalid.
     */
    public function filterDay($day)
    {
        if (! checkDateFormat('Ymd', $day)) {
            throw new Exception('Invalid date format');
        }

        $filtered = array();
        foreach ($this->links as $l) {
            if (startsWith($l['linkdate'], $day)) {
                $filtered[$l['linkdate']] = $l;
            }
        }
        ksort($filtered);
        return $filtered;
    }

    /**
     * Convert a list of tags (str) to an array. Also
     * - handle case sensitivity.
     * - accepts spaces commas as separator.
     *
     * @param string $tags          string containing a list of tags.
     * @param bool   $casesensitive will convert everything to lowercase if false.
     *
     * @return array filtered tags string.
    */
    public static function tagsStrToArray($tags, $casesensitive)
    {
        // We use UTF-8 conversion to handle various graphemes (i.e. cyrillic, or greek)
        $tagsOut = $casesensitive ? $tags : mb_convert_case($tags, MB_CASE_LOWER, 'UTF-8');
        $tagsOut = str_replace(',', ' ', $tagsOut);

        return array_values(array_filter(explode(' ', trim($tagsOut)), 'strlen'));
    }
}

class LinkNotFoundException extends Exception
{
    protected $message = 'The link you are trying to reach does not exist or has been deleted.';
}
