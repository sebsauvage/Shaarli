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
     * Test date_format() without time.
     */
    public function testDateFormatNoTime()
    {
        $date = DateTime::createFromFormat('Ymd_His', '20170101_101112');
        $this->assertRegExp('/1 janvier 2017/', format_date($date, false, true));
    }

    /**
     * Test date_format() using builtin PHP function strftime.
     */
    public function testDateFormatDefault()
    {
        $date = DateTime::createFromFormat('Ymd_His', '20170101_101112');
        $this->assertEquals('dim. 01 janv. 2017 10:11:12 EAT', format_date($date, true, false));
    }

    /**
     * Test date_format() using builtin PHP function strftime without time.
     */
    public function testDateFormatDefaultNoTime()
    {
        $date = DateTime::createFromFormat('Ymd_His', '20170201_101112');
        $this->assertEquals('01/02/2017', format_date($date, false, false));
    }

    /**
     * Test autoLocale with a simple value
     */
    public function testAutoLocaleValid()
    {
        $current = setlocale(LC_ALL, 0);
        $header = 'de-de';
        autoLocale($header);
        $this->assertEquals('de_DE.utf8', setlocale(LC_ALL, 0));

        setlocale(LC_ALL, $current);
    }

    /**
     * Test autoLocale with an alternative locale value
     */
    public function testAutoLocaleValidAlternative()
    {
        $current = setlocale(LC_ALL, 0);
        $header = 'de_de.UTF8';
        autoLocale($header);
        $this->assertEquals('de_DE.utf8', setlocale(LC_ALL, 0));

        setlocale(LC_ALL, $current);
    }

    /**
     * Test autoLocale with multiples value, the first one is valid
     */
    public function testAutoLocaleMultipleFirstValid()
    {
        $current = setlocale(LC_ALL, 0);
        $header = 'de-de;en-us';
        autoLocale($header);
        $this->assertEquals('de_DE.utf8', setlocale(LC_ALL, 0));

        setlocale(LC_ALL, $current);
    }

    /**
     * Test autoLocale with multiples value, the second one is available
     */
    public function testAutoLocaleMultipleSecondAvailable()
    {
        $current = setlocale(LC_ALL, 0);
        $header = 'mag_IN,de-de';
        autoLocale($header);
        $this->assertEquals('de_DE.utf8', setlocale(LC_ALL, 0));

        setlocale(LC_ALL, $current);
    }

    /**
     * Test autoLocale without value: defaults to en_US.
     */
    public function testAutoLocaleBlank()
    {
        $current = setlocale(LC_ALL, 0);
        autoLocale('');
        $this->assertEquals('en_US.utf8', setlocale(LC_ALL, 0));

        setlocale(LC_ALL, $current);
    }

    /**
     * Test autoLocale with an unavailable value: defaults to en_US.
     */
    public function testAutoLocaleUnavailable()
    {
        $current = setlocale(LC_ALL, 0);
        autoLocale('mag_IN');
        $this->assertEquals('en_US.utf8', setlocale(LC_ALL, 0));

        setlocale(LC_ALL, $current);
    }
}
