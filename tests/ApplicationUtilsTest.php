<?php
/**
 * ApplicationUtils' tests
 */

require_once 'application/ApplicationUtils.php';


/**
 * Unitary tests for Shaarli utilities
 */
class ApplicationUtilsTest extends PHPUnit_Framework_TestCase
{
    /**
     * Check supported PHP versions
     */
    public function testCheckSupportedPHPVersion()
    {
        $minVersion = '5.3';
        ApplicationUtils::checkPHPVersion($minVersion, '5.4.32');
        ApplicationUtils::checkPHPVersion($minVersion, '5.5');
        ApplicationUtils::checkPHPVersion($minVersion, '5.6.10');
    }

    /**
     * Check a unsupported PHP version
     * @expectedException              Exception
     * @expectedExceptionMessageRegExp /Your PHP version is obsolete/
     */
    public function testCheckSupportedPHPVersion51()
    {
        ApplicationUtils::checkPHPVersion('5.3', '5.1.0');
    }

    /**
     * Check another unsupported PHP version
     * @expectedException              Exception
     * @expectedExceptionMessageRegExp /Your PHP version is obsolete/
     */
    public function testCheckSupportedPHPVersion52()
    {
        ApplicationUtils::checkPHPVersion('5.3', '5.2');
    }

    /**
     * Checks resource permissions for the current Shaarli installation
     */
    public function testCheckCurrentResourcePermissions()
    {
        $config = array(
            'CACHEDIR' => 'cache',
            'CONFIG_FILE' => 'data/config.php',
            'DATADIR' => 'data',
            'DATASTORE' => 'data/datastore.php',
            'IPBANS_FILENAME' => 'data/ipbans.php',
            'LOG_FILE' => 'data/log.txt',
            'PAGECACHE' => 'pagecache',
            'RAINTPL_TMP' => 'tmp',
            'RAINTPL_TPL' => 'tpl',
            'UPDATECHECK_FILENAME' => 'data/lastupdatecheck.txt'
        );
        $this->assertEquals(
            array(),
            ApplicationUtils::checkResourcePermissions($config)
        );
    }

    /**
     * Checks resource permissions for a non-existent Shaarli installation
     */
    public function testCheckCurrentResourcePermissionsErrors()
    {
        $config = array(
            'CACHEDIR' => 'null/cache',
            'CONFIG_FILE' => 'null/data/config.php',
            'DATADIR' => 'null/data',
            'DATASTORE' => 'null/data/store.php',
            'IPBANS_FILENAME' => 'null/data/ipbans.php',
            'LOG_FILE' => 'null/data/log.txt',
            'PAGECACHE' => 'null/pagecache',
            'RAINTPL_TMP' => 'null/tmp',
            'RAINTPL_TPL' => 'null/tpl',
            'UPDATECHECK_FILENAME' => 'null/data/lastupdatecheck.txt'
        );
        $this->assertEquals(
            array(
                '"null/tpl" directory is not readable',
                '"null/cache" directory is not readable',
                '"null/cache" directory is not writable',
                '"null/data" directory is not readable',
                '"null/data" directory is not writable',
                '"null/pagecache" directory is not readable',
                '"null/pagecache" directory is not writable',
                '"null/tmp" directory is not readable',
                '"null/tmp" directory is not writable'
            ),
            ApplicationUtils::checkResourcePermissions($config)
        );
    }
}
