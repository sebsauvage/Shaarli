<?php

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
 *
 * Note: new events are put at the beginning of the file and history array.
 */
class History
{
    /**
     * @var string Action key: a new link has been created.
     */
    const CREATED = 'CREATED';

    /**
     * @var string Action key: a link has been updated.
     */
    const UPDATED = 'UPDATED';

    /**
     * @var string Action key: a link has been deleted.
     */
    const DELETED = 'DELETED';

    /**
     * @var string Action key: settings have been updated.
     */
    const SETTINGS = 'SETTINGS';

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
     * @param int    $retentionTime   History content rentention time in seconds.
     *
     * @throws Exception if something goes wrong.
     */
    public function __construct($historyFilePath, $retentionTime = null)
    {
        $this->historyFilePath = $historyFilePath;
        if ($retentionTime !== null) {
            $this->retentionTime = $retentionTime;
        }
        $this->check();
        $this->read();
    }

    /**
     * Add Event: new link.
     *
     * @param array $link Link data.
     */
    public function addLink($link)
    {
        $this->addEvent(self::CREATED, $link['id']);
    }

    /**
     * Add Event: update existing link.
     *
     * @param array $link Link data.
     */
    public function updateLink($link)
    {
        $this->addEvent(self::UPDATED, $link['id']);
    }

    /**
     * Add Event: delete existing link.
     *
     * @param array $link Link data.
     */
    public function deleteLink($link)
    {
        $this->addEvent(self::DELETED, $link['id']);
    }

    /**
     * Add Event: settings updated.
     */
    public function updateSettings()
    {
        $this->addEvent(self::SETTINGS);
    }

    /**
     * Save a new event and write it in the history file.
     *
     * @param string $status Event key, should be defined as constant.
     * @param mixed  $id     Event item identifier (e.g. link ID).
     */
    protected function addEvent($status, $id = null)
    {
        $item = [
            'event' => $status,
            'datetime' => (new DateTime())->format(DateTime::ATOM),
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
        if (! is_file($this->historyFilePath)) {
            FileUtils::writeFlatDB($this->historyFilePath, []);
        }

        if (! is_writable($this->historyFilePath)) {
            throw new Exception('History file isn\'t readable or writable');
        }
    }

    /**
     * Read JSON history file.
     */
    protected function read()
    {
        $this->history = FileUtils::readFlatDB($this->historyFilePath, []);
        if ($this->history === false) {
            throw new Exception('Could not parse history file');
        }
    }

    /**
     * Write JSON history file and delete old entries.
     */
    protected function write()
    {
        $comparaison = new DateTime('-'. $this->retentionTime . ' seconds');
        foreach ($this->history as $key => $value) {
            if (DateTime::createFromFormat(DateTime::ATOM, $value['datetime']) < $comparaison) {
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
        return $this->history;
    }
}
