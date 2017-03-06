<?php

require_once 'tests/UtilsTest.php';


class UtilsDeTest extends UtilsTest
{
    /**
     * Test date_format().
     */
    public function testDateFormat()
    {
        $date = DateTime::createFromFormat('Ymd_His', '20170101_101112');
        $this->assertRegExp('/1. Januar 2017 (um )?10:11:12 GMT\+0?3(:00)?/', format_date($date, true));
    }

    /**
     * Test date_format() using builtin PHP function strftime.
     */
    public function testDateFormatDefault()
    {
        $date = DateTime::createFromFormat('Ymd_His', '20170101_101112');
        $this->assertEquals('So 01 Jan 2017 10:11:12 EAT', format_date($date, false));
    }
}
