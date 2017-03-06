<?php

require_once 'tests/UtilsTest.php';


class UtilsEnTest extends UtilsTest
{
    /**
     * Test date_format().
     */
    public function testDateFormat()
    {
        $date = DateTime::createFromFormat('Ymd_His', '20170101_101112');
        $this->assertRegExp('/January 1, 2017 (at )?10:11:12 AM GMT\+0?3(:00)?/', format_date($date, true));
    }

    /**
     * Test date_format() using builtin PHP function strftime.
     */
    public function testDateFormatDefault()
    {
        $date = DateTime::createFromFormat('Ymd_His', '20170101_101112');
        $this->assertEquals('Sun 01 Jan 2017 10:11:12 AM EAT', format_date($date, false));
    }
}
