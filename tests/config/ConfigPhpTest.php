<?php

require_once 'application/config/ConfigPhp.php';

/**
 * Class ConfigPhpTest
 */
class ConfigPhpTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigPhp
     */
    protected $configIO;

    public function setUp()
    {
        $this->configIO = new ConfigPhp();
    }

    /**
     * Read a simple existing config file.
     */
    public function testRead()
    {
        $conf = $this->configIO->read('tests/config/php/configOK');
        $this->assertEquals('root', $conf['login']);
        $this->assertEquals('lala', $conf['redirector']);
        $this->assertEquals('data/datastore.php', $conf['config']['DATASTORE']);
        $this->assertEquals('1', $conf['plugins']['WALLABAG_VERSION']);
    }

    /**
     * Read a non existent config file -> empty array.
     */
    public function testReadNonExistent()
    {
        $this->assertEquals(array(), $this->configIO->read('nope'));
    }

    /**
     * Write a new config file.
     */
    public function testWriteNew()
    {
        $dataFile = 'tests/config/php/configWrite';
        $data = array(
            'login' => 'root',
            'redirector' => 'lala',
            'config' => array(
                'DATASTORE' => 'data/datastore.php',
            ),
            'plugins' => array(
                'WALLABAG_VERSION' => '1',
            )
        );
        $this->configIO->write($dataFile, $data);
        $expected = '<?php 
$GLOBALS[\'login\'] = \'root\';
$GLOBALS[\'redirector\'] = \'lala\';
$GLOBALS[\'config\'][\'DATASTORE\'] = \'data/datastore.php\';
$GLOBALS[\'plugins\'][\'WALLABAG_VERSION\'] = \'1\';
';
        $this->assertEquals($expected, file_get_contents($dataFile .'.php'));
        unlink($dataFile .'.php');
    }

    /**
     * Overwrite an existing setting.
     */
    public function testOverwrite()
    {
        $source = 'tests/config/php/configOK.php';
        $dest = 'tests/config/php/configOverwrite';
        copy($source, $dest . '.php');
        $conf = $this->configIO->read($dest);
        $conf['redirector'] = 'blabla';
        $this->configIO->write($dest, $conf);
        $conf = $this->configIO->read($dest);
        $this->assertEquals('blabla', $conf['redirector']);
        unlink($dest .'.php');
    }
}
