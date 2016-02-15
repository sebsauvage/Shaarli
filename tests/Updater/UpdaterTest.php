<?php

require_once 'tests/Updater/DummyUpdater.php';

/**
 * Class UpdaterTest.
 * Runs unit tests against the Updater class.
 */
class UpdaterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var array Configuration input set.
     */
    private static $configFields;

    /**
     * Executed before each test.
     */
    public function setUp()
    {
        self::$configFields = array(
            'login' => 'login',
            'hash' => 'hash',
            'salt' => 'salt',
            'timezone' => 'Europe/Paris',
            'title' => 'title',
            'titleLink' => 'titleLink',
            'redirector' => '',
            'disablesessionprotection' => false,
            'privateLinkByDefault' => false,
            'config' => array(
                'CONFIG_FILE' => 'tests/Updater/config.php',
                'DATADIR' => 'tests/Updater',
                'config1' => 'config1data',
                'config2' => 'config2data',
            )
        );
    }

    /**
     * Executed after each test.
     *
     * @return void
     */
    public function tearDown()
    {
        if (is_file(self::$configFields['config']['CONFIG_FILE'])) {
            unlink(self::$configFields['config']['CONFIG_FILE']);
        }

        if (is_file(self::$configFields['config']['DATADIR'] . '/options.php')) {
            unlink(self::$configFields['config']['DATADIR'] . '/options.php');
        }

        if (is_file(self::$configFields['config']['DATADIR'] . '/updates.json')) {
            unlink(self::$configFields['config']['DATADIR'] . '/updates.json');
        }
    }

    /**
     * Test read_updates_file with an empty/missing file.
     */
    public function testReadEmptyUpdatesFile()
    {
        $this->assertEquals(array(), read_updates_file(''));
        $updatesFile = self::$configFields['config']['DATADIR'] . '/updates.json';
        touch($updatesFile);
        $this->assertEquals(array(), read_updates_file($updatesFile));
    }

    /**
     * Test read/write updates file.
     */
    public function testReadWriteUpdatesFile()
    {
        $updatesFile = self::$configFields['config']['DATADIR'] . '/updates.json';
        $updatesMethods = array('m1', 'm2', 'm3');

        write_updates_file($updatesFile, $updatesMethods);
        $readMethods = read_updates_file($updatesFile);
        $this->assertEquals($readMethods, $updatesMethods);

        // Update
        $updatesMethods[] = 'm4';
        write_updates_file($updatesFile, $updatesMethods);
        $readMethods = read_updates_file($updatesFile);
        $this->assertEquals($readMethods, $updatesMethods);
    }

    /**
     * Test errors in write_updates_file(): empty updates file.
     *
     * @expectedException              Exception
     * @expectedExceptionMessageRegExp /Updates file path is not set(.*)/
     */
    public function testWriteEmptyUpdatesFile()
    {
        write_updates_file('', array('test'));
    }

    /**
     * Test errors in write_updates_file(): not writable updates file.
     *
     * @expectedException              Exception
     * @expectedExceptionMessageRegExp /Unable to write(.*)/
     */
    public function testWriteUpdatesFileNotWritable()
    {
        $updatesFile = self::$configFields['config']['DATADIR'] . '/updates.json';
        touch($updatesFile);
        chmod($updatesFile, 0444);
        @write_updates_file($updatesFile, array('test'));
    }

    /**
     * Test the update() method, with no update to run.
     *   1. Everything already run.
     *   2. User is logged out.
     */
    public function testNoUpdates()
    {
        $updates = array(
            'updateMethodDummy1',
            'updateMethodDummy2',
            'updateMethodDummy3',
            'updateMethodException',
        );
        $updater = new DummyUpdater($updates, array(), array(), true);
        $this->assertEquals(array(), $updater->update());

        $updater = new DummyUpdater(array(), array(), array(), false);
        $this->assertEquals(array(), $updater->update());
    }

    /**
     * Test the update() method, with all updates to run (except the failing one).
     */
    public function testUpdatesFirstTime()
    {
        $updates = array('updateMethodException',);
        $expectedUpdates = array(
            'updateMethodDummy1',
            'updateMethodDummy2',
            'updateMethodDummy3',
        );
        $updater = new DummyUpdater($updates, array(), array(), true);
        $this->assertEquals($expectedUpdates, $updater->update());
    }

    /**
     * Test the update() method, only one update to run.
     */
    public function testOneUpdate()
    {
        $updates = array(
            'updateMethodDummy1',
            'updateMethodDummy3',
            'updateMethodException',
        );
        $expectedUpdate = array('updateMethodDummy2');

        $updater = new DummyUpdater($updates, array(), array(), true);
        $this->assertEquals($expectedUpdate, $updater->update());
    }

    /**
     * Test Update failed.
     *
     * @expectedException UpdaterException
     */
    public function testUpdateFailed()
    {
        $updates = array(
            'updateMethodDummy1',
            'updateMethodDummy2',
            'updateMethodDummy3',
        );

        $updater = new DummyUpdater($updates, array(), array(), true);
        $updater->update();
    }

    /**
     * Test update mergeDeprecatedConfig:
     *      1. init a config file.
     *      2. init a options.php file with update value.
     *      3. merge.
     *      4. check updated value in config file.
     */
    public function testUpdateMergeDeprecatedConfig()
    {
        // init
        writeConfig(self::$configFields, true);
        $configCopy = self::$configFields;
        $invert = !$configCopy['privateLinkByDefault'];
        $configCopy['privateLinkByDefault'] = $invert;

        // Use writeConfig to create a options.php
        $configCopy['config']['CONFIG_FILE'] = 'tests/Updater/options.php';
        writeConfig($configCopy, true);

        $this->assertTrue(is_file($configCopy['config']['CONFIG_FILE']));

        // merge configs
        $updater = new Updater(array(), self::$configFields, array(), true);
        $updater->updateMethodMergeDeprecatedConfigFile();

        // make sure updated field is changed
        include self::$configFields['config']['CONFIG_FILE'];
        $this->assertEquals($invert, $GLOBALS['privateLinkByDefault']);
        $this->assertFalse(is_file($configCopy['config']['CONFIG_FILE']));
    }

    /**
     * Test mergeDeprecatedConfig in without options file.
     */
    public function testMergeDeprecatedConfigNoFile()
    {
        writeConfig(self::$configFields, true);

        $updater = new Updater(array(), self::$configFields, array(), true);
        $updater->updateMethodMergeDeprecatedConfigFile();

        include self::$configFields['config']['CONFIG_FILE'];
        $this->assertEquals(self::$configFields['login'], $GLOBALS['login']);
    }
}
