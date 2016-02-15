<?php

require_once 'application/Updater.php';

/**
 * Class DummyUpdater.
 * Extends Updater to add update method designed for unit tests.
 */
class DummyUpdater extends Updater
{
    /**
     * Object constructor.
     *
     * @param array   $doneUpdates Updates which are already done.
     * @param array   $config      Shaarli's configuration array.
     * @param LinkDB  $linkDB      LinkDB instance.
     * @param boolean $isLoggedIn  True if the user is logged in.
     */
    public function __construct($doneUpdates, $config, $linkDB, $isLoggedIn)
    {
        parent::__construct($doneUpdates, $config, $linkDB, $isLoggedIn);

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
    private final function updateMethodDummy1()
    {
        return true;
    }

    /**
     * Update method 2.
     *
     * @return bool true.
     */
    private final function updateMethodDummy2()
    {
        return true;
    }

    /**
     * Update method 3.
     *
     * @return bool true.
     */
    private final function updateMethodDummy3()
    {
        return true;
    }

    /**
     * Update method 4, raise an exception.
     *
     * @throws Exception error.
     */
    private final function updateMethodException()
    {
        throw new Exception('whatever');
    }
}
