<?php

namespace Shaarli\Bookmark\Exception;

class NotWritableDataStoreException extends \Exception
{
    /**
     * NotReadableDataStore constructor.
     *
     * @param string $dataStore file path
     */
    public function __construct($dataStore)
    {
        $this->message = 'Couldn\'t load data from the data store file "' . $dataStore . '". ' .
            'Your data might be corrupted, or your file isn\'t readable.';
    }
}
