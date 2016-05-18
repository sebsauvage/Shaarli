<?php

/**
 * Unit tests for Class ConfigManagerTest
 *
 * Note: it only test the manager with ConfigJson,
 *  ConfigPhp is only a workaround to handle the transition to JSON type.
 */
class ConfigManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigManager
     */
    protected $conf;

    public function setUp()
    {
        ConfigManager::$CONFIG_FILE = 'tests/config/config';
        $this->conf = ConfigManager::getInstance();
    }

    public function tearDown()
    {
        @unlink($this->conf->getConfigFile());
    }

    public function testSetWriteGet()
    {
        // This won't work with ConfigPhp.
        $this->markTestIncomplete();

        $this->conf->set('paramInt', 42);
        $this->conf->set('paramString', 'value1');
        $this->conf->set('paramBool', false);
        $this->conf->set('paramArray', array('foo' => 'bar'));
        $this->conf->set('paramNull', null);

        $this->conf->write(true);
        $this->conf->reload();

        $this->assertEquals(42, $this->conf->get('paramInt'));
        $this->assertEquals('value1', $this->conf->get('paramString'));
        $this->assertFalse($this->conf->get('paramBool'));
        $this->assertEquals(array('foo' => 'bar'), $this->conf->get('paramArray'));
        $this->assertEquals(null, $this->conf->get('paramNull'));
    }
    
}