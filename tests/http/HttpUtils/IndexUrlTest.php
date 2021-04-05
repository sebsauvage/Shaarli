<?php

/**
 * HttpUtils' tests
 */

namespace Shaarli\Http;

use Shaarli\TestCase;

require_once 'application/http/HttpUtils.php';

/**
 * Unitary tests for index_url()
 */
class IndexUrlTest extends TestCase
{
    /**
     * If on the main page, remove "index.php" from the URL resource
     */
    public function testRemoveIndex()
    {
        $this->assertEquals(
            'http://host.tld/',
            index_url(
                [
                    'HTTPS' => 'Off',
                    'SERVER_NAME' => 'host.tld',
                    'SERVER_PORT' => '80',
                    'SCRIPT_NAME' => '/index.php'
                ]
            )
        );

        $this->assertEquals(
            'http://host.tld/admin/',
            index_url(
                [
                    'HTTPS' => 'Off',
                    'SERVER_NAME' => 'host.tld',
                    'SERVER_PORT' => '80',
                    'SCRIPT_NAME' => '/admin/index.php'
                ]
            )
        );
    }

    /**
     * The resource is != "index.php"
     */
    public function testOtherResource()
    {
        $this->assertEquals(
            'http://host.tld/page.php',
            page_url(
                [
                    'HTTPS' => 'Off',
                    'SERVER_NAME' => 'host.tld',
                    'SERVER_PORT' => '80',
                    'SCRIPT_NAME' => '/page.php'
                ]
            )
        );

        $this->assertEquals(
            'http://host.tld/admin/page.php',
            page_url(
                [
                    'HTTPS' => 'Off',
                    'SERVER_NAME' => 'host.tld',
                    'SERVER_PORT' => '80',
                    'SCRIPT_NAME' => '/admin/page.php'
                ]
            )
        );
    }

    /**
     * The route is stored in REQUEST_URI
     */
    public function testPageUrlWithRoute()
    {
        $this->assertEquals(
            'http://host.tld/picture-wall',
            page_url(
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
            'http://host.tld/admin/picture-wall',
            page_url(
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

    /**
     * The route is stored in REQUEST_URI and subfolder
     */
    public function testPageUrlWithRouteUnderSubfolder()
    {
        $this->assertEquals(
            'http://host.tld/subfolder/picture-wall',
            page_url(
                [
                    'HTTPS' => 'Off',
                    'SERVER_NAME' => 'host.tld',
                    'SERVER_PORT' => '80',
                    'SCRIPT_NAME' => '/subfolder/index.php',
                    'REQUEST_URI' => '/subfolder/picture-wall',
                ]
            )
        );

        $this->assertEquals(
            'http://host.tld/subfolder/admin/picture-wall',
            page_url(
                [
                    'HTTPS' => 'Off',
                    'SERVER_NAME' => 'host.tld',
                    'SERVER_PORT' => '80',
                    'SCRIPT_NAME' => '/subfolder/admin/index.php',
                    'REQUEST_URI' => '/subfolder/admin/picture-wall',
                ]
            )
        );
    }
}
