<?php
/**
 * Unpares UrlUtils's tests
 */

namespace Shaarli\Http;

require_once 'application/http/UrlUtils.php';

/**
 * Unitary tests for unparse_url()
 */
class UnparseUrlTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Thanks for building nothing
     */
    public function testUnparseEmptyArray()
    {
        $this->assertEquals('', unparse_url(array()));
    }

    /**
     * Rebuild a full-featured URL
     */
    public function testUnparseFull()
    {
        $ref = 'http://username:password@hostname:9090/path'
              .'?arg1=value1&arg2=value2#anchor';
        $this->assertEquals($ref, unparse_url(parse_url($ref)));
    }
}
