<?php

namespace Shaarli;

use DateTime;
use Exception;
use Shaarli\Bookmark\Bookmark;
use Shaarli\Helper\FileUtils;

/**
 * Class History
 *
 * Handle the history file tracing events in Shaarli.
 * The history is stored as JSON in a file set by 'resource.history' setting.
 *
 * Available data:
 *   - event: event key
 *   - datetime: event date, in ISO8601 format.
 *   - id: event item identifier (currently only link IDs).
 *
 * Available event keys:
 *   - CREATED: new link
 *   - UPDATED: link updated
 *   - DELETED: link deleted
 *   - SETTINGS: the settings have been updated through the UI.
 *   - IMPORT: bulk bookmarks import
 *
 * Note: new events are put at the beginning of the file and history array.
 */
class History
{
    /**
     * @var string Action key: a new link has been created.
     */
    public const CREATED = 'CREATED';

    /**
     * @var string Action key: a link has been updated.
     */
    public const UPDATED = 'UPDATED';

    /**
     * @var string Action key: a link has been deleted.
     */
    public const DELETED = 'DELETED';

    /**
     * @var string Action key: settings have been updated.
     */
    public const SETTINGS = 'SETTINGS';

    /**
     * @var string Action key: a bulk import has been processed.
     */
    public const IMPORT = 'IMPORT';

    /**
     * @var string History file path.
     */
    protected $historyFilePath;

    /**
     * @var array History data.
     */
    protected $history;

    /**
     * @var int History retention time in seconds (1 month).
     */
    protected $retentionTime = 2678400;

    /**
     * History constructor.
     *
     * @param string $historyFilePath History file path.
     * @param int    $retentionTime   History content retention time in seconds.
     *
     * @throws Exception if something goes wrong.
     */
    public function __construct($historyFilePath, $retentionTime = null)
    {
        $this->historyFilePath = $historyFilePath;
        if ($retentionTime !== null) {
            $this->retentionTime = $retentionTime;
        }
    }

    /**
     * Initialize: read history file.
     *
     * Allow lazy loading (don't read the file if it isn't necessary).
     */
    protected function initialize()
    {
        $this->check();
        $this->read();
    }

    /**
     * Add Event: new link.
     *
     * @param Bookmark $link Link data.
     */
    public function addLink($link)
    {
        $this->addEvent(self::CREATED, $link->getId());
    }

    /**
     * Add Event: update existing link.
     *
     * @param Bookmark $link Link data.
     */
    public function updateLink($link)
    {
        $this->addEvent(self::UPDATED, $link->getId());
    }

    /**
     * Add Event: delete existing link.
     *
     * @param Bookmark $link Link data.
     */
    public function deleteLink($link)
    {
        $this->addEvent(self::DELETED, $link->getId());
    }

    /**
     * Add Event: settings updated.
     */
    public function updateSettings()
    {
        $this->addEvent(self::SETTINGS);
    }

    /**
     * Add Event: bulk import.
     *
     * Note: we don't store bookmarks add/update one by one since it can have a huge impact on performances.
     */
    public function importLinks()
    {
        $this->addEvent(self::IMPORT);
    }

    /**
     * Save a new event and write it in the history file.
     *
     * @param string $status Event key, should be defined as constant.
     * @param mixed  $id     Event item identifier (e.g. link ID).
     */
    protected function addEvent($status, $id = null)
    {
        if ($this->history === null) {
            $this->initialize();
        }

        $item = [
            'event' => $status,
            'datetime' => new DateTime(),
            'id' => $id !== null ? $id : '',
        ];
        $this->history = array_merge([$item], $this->history);
        $this->write();
    }

    /**
     * Check that the history file is writable.
     * Create the file if it doesn't exist.
     *
     * @throws Exception if it isn't writable.
     */
    protected function check()
    {
        if (!is_file($this->historyFilePath)) {
            FileUtils::writeFlatDB($this->historyFilePath, []);
        }

        if (!is_writable($this->historyFilePath)) {
            throw new Exception(t('History file isn\'t readable or writable'));
        }
    }

    /**
     * Read JSON history file.
     */
    protected function read()
    {
        $this->history = FileUtils::readFlatDB($this->historyFilePath, []);
        if ($this->history === false) {
            throw new Exception(t('Could not parse history file'));
        }
    }

    /**
     * Write JSON history file and delete old entries.
     */
    protected function write()
    {
        $comparaison = new DateTime('-' . $this->retentionTime . ' seconds');
        foreach ($this->history as $key => $value) {
            if ($value['datetime'] < $comparaison) {
                unset($this->history[$key]);
            }
        }
        FileUtils::writeFlatDB($this->historyFilePath, array_values($this->history));
    }

    /**
     * Get the History.
     *
     * @return array
     */
    public function getHistory()
    {
        if ($this->history === null) {
            $this->initialize();
        }

        return $this->history;
    }
}
