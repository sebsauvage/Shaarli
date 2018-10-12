<?php
/**
 * Unpares Url's tests
 */

require_once 'application/Url.php';

/**
 * Unitary tests for unparse_url()
 */
class UnparseUrlTest extends PHPUnit_Framework_TestCase
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
