<?php
namespace Shaarli\Updater;

use Exception;
use ReflectionClass;
use ReflectionMethod;
use Shaarli\Bookmark\BookmarkFileService;
use Shaarli\Bookmark\LinkDB;
use Shaarli\Config\ConfigManager;

/**
 * Class DummyUpdater.
 * Extends updater to add update method designed for unit tests.
 */
class DummyUpdater extends Updater
{
    /**
     * Object constructor.
     *
     * @param array               $doneUpdates     Updates which are already done.
     * @param BookmarkFileService $bookmarkService LinkDB instance.
     * @param ConfigManager       $conf            Configuration Manager instance.
     * @param boolean             $isLoggedIn      True if the user is logged in.
     */
    public function __construct($doneUpdates, $bookmarkService, $conf, $isLoggedIn)
    {
        parent::__construct($doneUpdates, $bookmarkService, $conf, $isLoggedIn);

        // Retrieve all update methods.
        // For unit test, only retrieve final methods,
        $class = new ReflectionClass($this);
        $this->methods = $class->getMethods(ReflectionMethod::IS_FINAL);
    }

    /**
     * Update method 1.
     *
     * @return bool true.
     */
    final protected function updateMethodDummy1()
    {
        return true;
    }

    /**
     * Update method 2.
     *
     * @return bool true.
     */
    final protected function updateMethodDummy2()
    {
        return true;
    }

    /**
     * Update method 3.
     *
     * @return bool true.
     */
    final protected function updateMethodDummy3()
    {
        return true;
    }

    /**
     * Update method 4, raise an exception.
     *
     * @throws Exception error.
     */
    final protected function updateMethodException()
    {
        throw new Exception('whatever');
    }
}
