<?php
/**
 * TimeZone's tests
 */

require_once 'application/TimeZone.php';

/**
 * Unitary tests for timezone utilities
 */
class TimeZoneTest extends PHPUnit_Framework_TestCase
{
    /**
     * Generate a timezone selection form
     */
    public function testGenerateTimeZoneForm()
    {
        $generated = generateTimeZoneForm();

        // HTML form
        $this->assertStringStartsWith('Continent:<select', $generated[0]);
        $this->assertContains('selected="selected"', $generated[0]);
        $this->assertStringEndsWith('</select><br />', $generated[0]);

        // Javascript handler
        $this->assertStringStartsWith('<script>', $generated[1]);
        $this->assertContains(
            '<option value=\"Bermuda\">Bermuda<\/option>',
            $generated[1]
        );
        $this->assertStringEndsWith('</script>', $generated[1]);
    }

    /**
     * Generate a timezone selection form, with a preselected timezone
     */
    public function testGenerateTimeZoneFormPreselected()
    {
        $generated = generateTimeZoneForm('Antarctica/Syowa');

        // HTML form
        $this->assertStringStartsWith('Continent:<select', $generated[0]);
        $this->assertContains(
            'value="Antarctica" selected="selected"',
            $generated[0]
        );
        $this->assertContains(
            'value="Syowa" selected="selected"',
            $generated[0]
        );
        $this->assertStringEndsWith('</select><br />', $generated[0]);


        // Javascript handler
        $this->assertStringStartsWith('<script>', $generated[1]);
        $this->assertContains(
            '<option value=\"Bermuda\">Bermuda<\/option>',
            $generated[1]
        );
        $this->assertStringEndsWith('</script>', $generated[1]);
    }

    /**
     * Check valid timezones
     */
    public function testValidTimeZone()
    {
        $this->assertTrue(isTimeZoneValid('America', 'Argentina/Ushuaia'));
        $this->assertTrue(isTimeZoneValid('Europe', 'Oslo'));
        $this->assertTrue(isTimeZoneValid('UTC', 'UTC'));
    }

    /**
     * Check invalid timezones
     */
    public function testInvalidTimeZone()
    {
        $this->assertFalse(isTimeZoneValid('CEST', 'CEST'));
        $this->assertFalse(isTimeZoneValid('Europe', 'Atlantis'));
        $this->assertFalse(isTimeZoneValid('Middle_Earth', 'Moria'));
    }
}
