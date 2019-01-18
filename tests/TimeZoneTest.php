<?php
/**
 * TimeZone's tests
 */

require_once 'application/TimeZone.php';

/**
 * Unitary tests for timezone utilities
 */
class TimeZoneTest extends PHPUnit\Framework\TestCase
{
    /**
     * @var array of timezones
     */
    protected $installedTimezones;

    public function setUp()
    {
        $this->installedTimezones = [
            'Antarctica/Syowa',
            'Europe/London',
            'Europe/Paris',
            'UTC'
        ];
    }

    /**
     * Generate a timezone selection form
     */
    public function testGenerateTimeZoneForm()
    {
        $expected = [
            'continents' => [
                'Antarctica',
                'Europe',
                'UTC',
                'selected' => '',
            ],
            'cities' => [
                ['continent' => 'Antarctica', 'city' => 'Syowa'],
                ['continent' => 'Europe',     'city' => 'London'],
                ['continent' => 'Europe',     'city' => 'Paris'],
                ['continent' => 'UTC',        'city' => 'UTC'],
                'selected'    => '',
            ]
        ];

        list($continents, $cities) = generateTimeZoneData($this->installedTimezones);

        $this->assertEquals($expected['continents'], $continents);
        $this->assertEquals($expected['cities'], $cities);
    }

    /**
     * Generate a timezone selection form, with a preselected timezone
     */
    public function testGenerateTimeZoneFormPreselected()
    {
        $expected = [
            'continents' => [
                'Antarctica',
                'Europe',
                'UTC',
                'selected' => 'Antarctica',
            ],
            'cities' => [
                ['continent' => 'Antarctica', 'city' => 'Syowa'],
                ['continent' => 'Europe',     'city' => 'London'],
                ['continent' => 'Europe',     'city' => 'Paris'],
                ['continent' => 'UTC',        'city' => 'UTC'],
                'selected'   => 'Syowa',
            ]
        ];

        list($continents, $cities) = generateTimeZoneData($this->installedTimezones, 'Antarctica/Syowa');

        $this->assertEquals($expected['continents'], $continents);
        $this->assertEquals($expected['cities'], $cities);
    }

    /**
     * Check valid timezones
     */
    public function testValidTimeZone()
    {
        $this->assertTrue(isTimeZoneValid('America', 'Argentina/Ushuaia'));
        $this->assertTrue(isTimeZoneValid('Europe', 'Oslo'));
    }

    /**
     * Check invalid timezones
     */
    public function testInvalidTimeZone()
    {
        $this->assertFalse(isTimeZoneValid('CEST', 'CEST'));
        $this->assertFalse(isTimeZoneValid('Europe', 'Atlantis'));
        $this->assertFalse(isTimeZoneValid('Middle_Earth', 'Moria'));
        $this->assertFalse(isTimeZoneValid('UTC', 'UTC'));
    }
}
