<?php

require_once 'tests/UtilsTest.php';


class UtilsFrTest extends UtilsTest
{
    /**
     * Test date_format().
     */
    public function testDateFormat()
    {
        $date = DateTime::createFromFormat('Ymd_His', '20170101_101112');
        $this->assertRegExp('/1 janvier 2017 (à )?10:11:12 UTC\+0?3(:00)?/', format_date($date));
    }

    /**
     * Test date_format() using builtin PHP function strftime.
     */
    public function testDateFormatDefault()
    {
        $date = DateTime::createFromFormat('Ymd_His', '20170101_101112');
        $this->assertEquals('dim. 01 janv. 2017 10:11:12 EAT', format_date($date, false));
    }
}
