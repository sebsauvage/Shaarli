<?php

declare(strict_types=1);

namespace Shaarli\Http;

use Shaarli\TestCase;

/**
 * Test index_url with SHAARLI_ROOT_URL defined to override automatic retrieval.
 * This should stay in its dedicated class to make sure to not alter other tests of the suite.
 */
class IndexUrlTestWithConstant extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        define('SHAARLI_ROOT_URL', 'http://other-host.tld/subfolder/');
    }

    /**
     * The route is stored in REQUEST_URI and subfolder
     */
    public function testIndexUrlWithConstantDefined()
    {
        $this->assertEquals(
            'http://other-host.tld/subfolder/',
            index_url(
                [
                    'HTTPS' => 'Off',
                    'SERVER_NAME' => 'host.tld',
                    'SERVER_PORT' => '80',
                    'SCRIPT_NAME' => '/index.php',
                    'REQUEST_URI' => '/picture-wall',
                ]
            )
        );

        $this->assertEquals(
            'http://other-host.tld/subfolder/',
            index_url(
                [
                    'HTTPS' => 'Off',
                    'SERVER_NAME' => 'host.tld',
                    'SERVER_PORT' => '80',
                    'SCRIPT_NAME' => '/admin/index.php',
                    'REQUEST_URI' => '/admin/picture-wall',
                ]
            )
        );
    }
}
