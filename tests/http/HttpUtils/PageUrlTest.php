<?php
/**
 * HttpUtils' tests
 */

namespace Shaarli\Http;

require_once 'application/http/HttpUtils.php';

/**
 * Unitary tests for page_url()
 */
class PageUrlTest extends \PHPUnit\Framework\TestCase
{
    /**
     * If on the main page, remove "index.php" from the URL resource
     */
    public function testRemoveIndex()
    {
        $this->assertEquals(
            'http://host.tld/?p1=v1&p2=v2',
            page_url(
                array(
                    'HTTPS' => 'Off',
                    'SERVER_NAME' => 'host.tld',
                    'SERVER_PORT' => '80',
                    'SCRIPT_NAME' => '/index.php',
                    'QUERY_STRING' => 'p1=v1&p2=v2'
                )
            )
        );

        $this->assertEquals(
            'http://host.tld/admin/?action=edit_tag',
            page_url(
                array(
                    'HTTPS' => 'Off',
                    'SERVER_NAME' => 'host.tld',
                    'SERVER_PORT' => '80',
                    'SCRIPT_NAME' => '/admin/index.php',
                    'QUERY_STRING' => 'action=edit_tag'
                )
            )
        );
    }

    /**
     * The resource is != "index.php"
     */
    public function testOtherResource()
    {
        $this->assertEquals(
            'http://host.tld/page.php?p1=v1&p2=v2',
            page_url(
                array(
                    'HTTPS' => 'Off',
                    'SERVER_NAME' => 'host.tld',
                    'SERVER_PORT' => '80',
                    'SCRIPT_NAME' => '/page.php',
                    'QUERY_STRING' => 'p1=v1&p2=v2'
                )
            )
        );

        $this->assertEquals(
            'http://host.tld/admin/page.php?action=edit_tag',
            page_url(
                array(
                    'HTTPS' => 'Off',
                    'SERVER_NAME' => 'host.tld',
                    'SERVER_PORT' => '80',
                    'SCRIPT_NAME' => '/admin/page.php',
                    'QUERY_STRING' => 'action=edit_tag'
                )
            )
        );
    }
}
