<?php

namespace Shaarli\Updater;

class UpdaterUtils
{
    /**
     * Read the updates file, and return already done updates.
     *
     * @param string $updatesFilepath Updates file path.
     *
     * @return array Already done update methods.
     */
    public static function readUpdatesFile($updatesFilepath)
    {
        if (! empty($updatesFilepath) && is_file($updatesFilepath)) {
            $content = file_get_contents($updatesFilepath);
            if (! empty($content)) {
                return explode(';', $content);
            }
        }
        return [];
    }

    /**
     * Write updates file.
     *
     * @param string $updatesFilepath Updates file path.
     * @param array  $updates         Updates array to write.
     *
     * @throws \Exception Couldn't write version number.
     */
    public static function writeUpdatesFile($updatesFilepath, $updates)
    {
        if (empty($updatesFilepath)) {
            throw new \Exception('Updates file path is not set, can\'t write updates.');
        }

        $res = file_put_contents($updatesFilepath, implode(';', $updates));
        if ($res === false) {
            throw new \Exception('Unable to write updates in ' . $updatesFilepath . '.');
        }
    }
}
