<?php
namespace Shaarli\Config;

/**
 * Class ConfigPhpTest
 */
class ConfigPhpTest extends \PHPUnit\Framework\TestCase
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
        $conf = $this->configIO->read('tests/utils/config/configPhp.php');
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
     * Read an empty existent config file -> array with blank default values.
     */
    public function testReadEmpty()
    {
        $dataFile = 'tests/utils/config/emptyConfigPhp.php';
        $conf = $this->configIO->read($dataFile);
        $this->assertEmpty($conf['login']);
        $this->assertEmpty($conf['title']);
        $this->assertEmpty($conf['titleLink']);
        $this->assertEmpty($conf['config']);
        $this->assertEmpty($conf['plugins']);
    }

    /**
     * Write a new config file.
     */
    public function testWriteNew()
    {
        $dataFile = 'tests/utils/config/configWrite.php';
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
        $this->assertEquals($expected, file_get_contents($dataFile));
        unlink($dataFile);
    }

    /**
     * Overwrite an existing setting.
     */
    public function testOverwrite()
    {
        $source = 'tests/utils/config/configPhp.php';
        $dest = 'tests/utils/config/configOverwrite.php';
        copy($source, $dest);
        $conf = $this->configIO->read($dest);
        $conf['redirector'] = 'blabla';
        $this->configIO->write($dest, $conf);
        $conf = $this->configIO->read($dest);
        $this->assertEquals('blabla', $conf['redirector']);
        unlink($dest);
    }
}
