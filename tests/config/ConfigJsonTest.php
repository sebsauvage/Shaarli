<?php
namespace Shaarli\Config;

/**
 * Class ConfigJsonTest
 */
class ConfigJsonTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ConfigJson
     */
    protected $configIO;

    public function setUp()
    {
        $this->configIO = new ConfigJson();
    }

    /**
     * Read a simple existing config file.
     */
    public function testRead()
    {
        $conf = $this->configIO->read('tests/utils/config/configJson.json.php');
        $this->assertEquals('root', $conf['credentials']['login']);
        $this->assertEquals('lala', $conf['redirector']['url']);
        $this->assertEquals('tests/utils/config/datastore.php', $conf['resource']['datastore']);
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
     * Read a non existent config file -> empty array.
     *
     * @expectedException \Exception
     * @expectedExceptionMessageRegExp  /An error occurred while parsing JSON configuration file \([\w\/\.]+\): error code #4/
     */
    public function testReadInvalidJson()
    {
        $this->configIO->read('tests/utils/config/configInvalid.json.php');
    }

    /**
     * Write a new config file.
     */
    public function testWriteNew()
    {
        $dataFile = 'tests/utils/config/configWrite.json.php';
        $data = array(
            'credentials' => array(
                'login' => 'root',
            ),
            'resource' => array(
                'datastore' => 'data/datastore.php',
            ),
            'redirector' => array(
                'url' => 'lala',
            ),
            'plugins' => array(
                'WALLABAG_VERSION' => '1',
            )
        );
        $this->configIO->write($dataFile, $data);
        // PHP 5.3 doesn't support json pretty print.
        if (defined('JSON_PRETTY_PRINT')) {
            $expected = '{
    "credentials": {
        "login": "root"
    },
    "resource": {
        "datastore": "data\/datastore.php"
    },
    "redirector": {
        "url": "lala"
    },
    "plugins": {
        "WALLABAG_VERSION": "1"
    }
}';
        } else {
            $expected = '{"credentials":{"login":"root"},"resource":{"datastore":"data\/datastore.php"},"redirector":{"url":"lala"},"plugins":{"WALLABAG_VERSION":"1"}}';
        }
        $expected = ConfigJson::getPhpHeaders() . $expected . ConfigJson::getPhpSuffix();
        $this->assertEquals($expected, file_get_contents($dataFile));
        unlink($dataFile);
    }

    /**
     * Overwrite an existing setting.
     */
    public function testOverwrite()
    {
        $source = 'tests/utils/config/configJson.json.php';
        $dest = 'tests/utils/config/configOverwrite.json.php';
        copy($source, $dest);
        $conf = $this->configIO->read($dest);
        $conf['redirector']['url'] = 'blabla';
        $this->configIO->write($dest, $conf);
        $conf = $this->configIO->read($dest);
        $this->assertEquals('blabla', $conf['redirector']['url']);
        unlink($dest);
    }

    /**
     * Write to invalid path.
     *
     * @expectedException \Shaarli\Exceptions\IOException
     */
    public function testWriteInvalidArray()
    {
        $conf = array('conf' => 'value');
        @$this->configIO->write(array(), $conf);
    }

    /**
     * Write to invalid path.
     *
     * @expectedException \Shaarli\Exceptions\IOException
     */
    public function testWriteInvalidBlank()
    {
        $conf = array('conf' => 'value');
        @$this->configIO->write('', $conf);
    }
}
