<?php

/**
 * Generates a list of available timezone continents and cities.
 *
 * Two distinct array based on available timezones
 * and the one selected in the settings:
 *   - (0) continents:
 *     + list of available continents
 *     + special key 'selected' containing the value of the selected timezone's continent
 *   - (1) cities:
 *     + list of available cities associated with their continent
 *     + special key 'selected' containing the value of the selected timezone's city (without the continent)
 *
 * Example:
 *   [
 *     [
 *       'America',
 *       'Europe',
 *       'selected' => 'Europe',
 *     ],
 *     [
 *       ['continent' => 'America', 'city' => 'Toronto'],
 *       ['continent' => 'Europe', 'city' => 'Paris'],
 *       'selected' => 'Paris',
 *     ],
 *   ];
 *
 * Notes:
 *   - 'UTC/UTC' is mapped to 'UTC' to form a valid option
 *   - a few timezone cities includes the country/state, such as Argentina/Buenos_Aires
 *   - these arrays are designed to build timezone selects in template files with any HTML structure
 *
 * @param array  $installedTimeZones  List of installed timezones as string
 * @param string $preselectedTimezone preselected timezone (optional)
 *
 * @return array[] continents and cities
 **/
function generateTimeZoneData($installedTimeZones, $preselectedTimezone = '')
{
    if ($preselectedTimezone == 'UTC') {
        $pcity = $pcontinent = 'UTC';
    } else {
        // Try to split the provided timezone
        $spos = strpos($preselectedTimezone, '/');
        $pcontinent = substr($preselectedTimezone, 0, $spos);
        $pcity = substr($preselectedTimezone, $spos + 1);
    }

    $continents = [];
    $cities = [];
    foreach ($installedTimeZones as $tz) {
        if ($tz == 'UTC') {
            $tz = 'UTC/UTC';
        }
        $spos = strpos($tz, '/');

        // Ignore invalid timezones
        if ($spos === false) {
            continue;
        }

        $continent = substr($tz, 0, $spos);
        $city = substr($tz, $spos + 1);
        $cities[] = ['continent' => $continent, 'city' => $city];
        $continents[$continent] = true;
    }

    $continents = array_keys($continents);
    $continents['selected'] = $pcontinent;
    $cities['selected'] = $pcity;

    return [$continents, $cities];
}

/**
 * Tells if a continent/city pair form a valid timezone
 *
 * Note: 'UTC/UTC' is mapped to 'UTC'
 *
 * @param string $continent the timezone continent
 * @param string $city      the timezone city
 *
 * @return bool whether continent/city is a valid timezone
 */
function isTimeZoneValid($continent, $city)
{
    return in_array(
        $continent . '/' . $city,
        timezone_identifiers_list()
    );
}
