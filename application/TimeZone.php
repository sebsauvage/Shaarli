<?php
/**
 * Generates the timezone selection form and JavaScript.
 *
 * Note: 'UTC/UTC' is mapped to 'UTC' to form a valid option
 *
 * Example: preselect Europe/Paris
 *  list($htmlform, $js) = generateTimeZoneForm('Europe/Paris');
 *
 * @param string $preselectedTimezone preselected timezone (optional)
 *
 * @return array containing the generated HTML form and Javascript code
 **/
function generateTimeZoneForm($preselectedTimezone='')
{
    // Select the server timezone
    if ($preselectedTimezone == '') {
        $preselectedTimezone = date_default_timezone_get();
    }

    if ($preselectedTimezone == 'UTC') {
        $pcity = $pcontinent = 'UTC';
    } else {
        // Try to split the provided timezone
        $spos = strpos($preselectedTimezone, '/');
        $pcontinent = substr($preselectedTimezone, 0, $spos);
        $pcity = substr($preselectedTimezone, $spos+1);
    }

    // The list is in the form 'Europe/Paris', 'America/Argentina/Buenos_Aires'
    // We split the list in continents/cities.
    $continents = array();
    $cities = array();

    // TODO: use a template to generate the HTML/Javascript form

    foreach (timezone_identifiers_list() as $tz) {
        if ($tz == 'UTC') {
            $tz = 'UTC/UTC';
        }
        $spos = strpos($tz, '/');

        if ($spos !== false) {
            $continent = substr($tz, 0, $spos);
            $city = substr($tz, $spos+1);
            $continents[$continent] = 1;

            if (!isset($cities[$continent])) {
                $cities[$continent] = '';
            }
            $cities[$continent] .= '<option value="'.$city.'"';
            if ($pcity == $city) {
                $cities[$continent] .= ' selected="selected"';
            }
            $cities[$continent] .= '>'.$city.'</option>';
        }
    }

    $continentsHtml = '';
    $continents = array_keys($continents);

    foreach ($continents as $continent) {
        $continentsHtml .= '<option  value="'.$continent.'"';
        if ($pcontinent == $continent) {
            $continentsHtml .= ' selected="selected"';
        }
        $continentsHtml .= '>'.$continent.'</option>';
    }

    // Timezone selection form
    $timezoneForm = 'Continent:';
    $timezoneForm .= '<select name="continent" id="continent" onChange="onChangecontinent();">';
    $timezoneForm .= $continentsHtml.'</select>';
    $timezoneForm .= '&nbsp;&nbsp;&nbsp;&nbsp;City:';
    $timezoneForm .= '<select name="city" id="city">'.$cities[$pcontinent].'</select><br />';

    // Javascript handler - updates the city list when the user selects a continent
    $timezoneJs = '<script>';
    $timezoneJs .= 'function onChangecontinent() {';
    $timezoneJs .= 'document.getElementById("city").innerHTML =';
    $timezoneJs .= ' citiescontinent[document.getElementById("continent").value]; }';
    $timezoneJs .= 'var citiescontinent = '.json_encode($cities).';';
    $timezoneJs .= '</script>';

    return array($timezoneForm, $timezoneJs);
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
        $continent.'/'.$city,
        timezone_identifiers_list()
    );
}
