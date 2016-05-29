<?php

/**
 * Unit tests for Class ConfigManagerTest
 *
 * Note: it only test the manager with ConfigJson,
 *  ConfigPhp is only a workaround to handle the transition to JSON type.
 */
class ConfigManagerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigManager
     */
    protected $conf;

    public function setUp()
    {
        ConfigManager::$CONFIG_FILE = 'tests/utils/config/configJson';
        $this->conf = ConfigManager::reset();
    }

    /**
     * Simple config test:
     *   1. Set settings.
     *   2. Check settings value.
     */
    public function testSetGet()
    {
        $this->conf->set('paramInt', 42);
        $this->conf->set('paramString', 'value1');
        $this->conf->set('paramBool', false);
        $this->conf->set('paramArray', array('foo' => 'bar'));
        $this->conf->set('paramNull', null);

        $this->assertEquals(42, $this->conf->get('paramInt'));
        $this->assertEquals('value1', $this->conf->get('paramString'));
        $this->assertFalse($this->conf->get('paramBool'));
        $this->assertEquals(array('foo' => 'bar'), $this->conf->get('paramArray'));
        $this->assertEquals(null, $this->conf->get('paramNull'));
    }

    /**
     * Set/write/get config test:
     *   1. Set settings.
     *   2. Write it to the config file.
     *   3. Read the file.
     *   4. Check settings value.
     */
    public function testSetWriteGet()
    {
        $this->conf->set('paramInt', 42);
        $this->conf->set('paramString', 'value1');
        $this->conf->set('paramBool', false);
        $this->conf->set('paramArray', array('foo' => 'bar'));
        $this->conf->set('paramNull', null);

        ConfigManager::$CONFIG_FILE = 'tests/utils/config/configTmp';
        $this->conf->write(true);
        $this->conf->reload();
        unlink($this->conf->getConfigFile());

        $this->assertEquals(42, $this->conf->get('paramInt'));
        $this->assertEquals('value1', $this->conf->get('paramString'));
        $this->assertFalse($this->conf->get('paramBool'));
        $this->assertEquals(array('foo' => 'bar'), $this->conf->get('paramArray'));
        $this->assertEquals(null, $this->conf->get('paramNull'));
    }

    /**
     * Test set/write/get with nested keys.
     */
    public function testSetWriteGetNested()
    {
        $this->conf->set('foo.bar.key.stuff', 'testSetWriteGetNested');

        ConfigManager::$CONFIG_FILE = 'tests/utils/config/configTmp';
        $this->conf->write(true);
        $this->conf->reload();
        unlink($this->conf->getConfigFile());

        $this->assertEquals('testSetWriteGetNested', $this->conf->get('foo.bar.key.stuff'));
    }

    /**
     * Set with an empty key.
     *
     * @expectedException Exception
     * @expectedExceptionMessageRegExp #^Invalid setting key parameter. String expected, got.*#
     */
    public function testSetEmptyKey()
    {
        $this->conf->set('', 'stuff');
    }

    /**
     * Set with an array key.
     *
     * @expectedException Exception
     * @expectedExceptionMessageRegExp #^Invalid setting key parameter. String expected, got.*#
     */
    public function testSetArrayKey()
    {
        $this->conf->set(array('foo' => 'bar'), 'stuff');
    }

    /**
     * Try to write the config without mandatory parameter (e.g. 'login').
     *
     * @expectedException MissingFieldConfigException
     */
    public function testWriteMissingParameter()
    {
        ConfigManager::$CONFIG_FILE = 'tests/utils/config/configTmp';
        $this->assertFalse(file_exists($this->conf->getConfigFile()));
        $this->conf->reload();

        $this->conf->write(true);
    }

    /**
     * Try to get non existent config keys.
     */
    public function testGetNonExistent()
    {
        $this->assertEquals('', $this->conf->get('nope.test'));
        $this->assertEquals('default', $this->conf->get('nope.test', 'default'));
    }

    /**
     * Test the 'exists' method with existent values.
     */
    public function testExistsOk()
    {
        $this->assertTrue($this->conf->exists('login'));
        $this->assertTrue($this->conf->exists('config.foo'));
    }

    /**
     * Test the 'exists' method with non existent or invalid values.
     */
    public function testExistsKo()
    {
        $this->assertFalse($this->conf->exists('nope'));
        $this->assertFalse($this->conf->exists('nope.nope'));
        $this->assertFalse($this->conf->exists(''));
        $this->assertFalse($this->conf->exists(false));
    }

    /**
     * Reset the ConfigManager instance.
     */
    public function testReset()
    {
        $conf = $this->conf;
        $this->assertTrue($conf === ConfigManager::getInstance());
        $this->assertFalse($conf === $this->conf->reset());
        $this->assertFalse($conf === ConfigManager::getInstance());
    }

    /**
     * Reload the config from file.
     */
    public function testReload()
    {
        ConfigManager::$CONFIG_FILE = 'tests/utils/config/configTmp';
        $newConf = ConfigJson::$PHP_HEADER . '{ "key": "value" }';
        file_put_contents($this->conf->getConfigFile(), $newConf);
        $this->conf->reload();
        unlink($this->conf->getConfigFile());
        // Previous conf no longer exists, and new values have been loaded.
        $this->assertFalse($this->conf->exists('login'));
        $this->assertEquals('value', $this->conf->get('key'));
    }
}
