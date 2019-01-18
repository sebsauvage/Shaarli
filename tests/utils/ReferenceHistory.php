<?php

use Shaarli\FileUtils;
use Shaarli\History;

/**
 * Populates a reference history
 */
class ReferenceHistory
{
    private $count;

    private $history = [];

    /**
     * Populates the test DB with reference data
     */
    public function __construct()
    {
        $this->addEntry(
            History::DELETED,
            DateTime::createFromFormat('Ymd_His', '20170303_121216'),
            124
        );

        $this->addEntry(
            History::SETTINGS,
            DateTime::createFromFormat('Ymd_His', '20170302_121215')
        );

        $this->addEntry(
            History::UPDATED,
            DateTime::createFromFormat('Ymd_His', '20170301_121214'),
            123
        );

        $this->addEntry(
            History::CREATED,
            DateTime::createFromFormat('Ymd_His', '20170201_121214'),
            124
        );

        $this->addEntry(
            History::CREATED,
            DateTime::createFromFormat('Ymd_His', '20170101_121212'),
            123
        );
    }

    /**
     * Adds a new history entry
     *
     * @param string   $event    Event identifier
     * @param DateTime $datetime creation date
     * @param int      $id       optional: related link ID
     */
    protected function addEntry($event, $datetime, $id = null)
    {
        $link = [
            'event' => $event,
            'datetime' => $datetime,
            'id' => $id,
        ];
        $this->history[] = $link;
        $this->count++;
    }

    /**
     * Writes data to the datastore
     *
     * @param string $filename write history content to.
     */
    public function write($filename)
    {
        FileUtils::writeFlatDB($filename, $this->history);
    }

    /**
     * Returns the number of links in the reference data
     */
    public function count()
    {
        return $this->count;
    }
}
