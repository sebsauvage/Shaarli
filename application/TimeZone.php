<?php
/**
 * Generates the timezone selection form and JavaScript.
 *
 * Note: 'UTC/UTC' is mapped to 'UTC' to form a valid option
 *
 * Example: preselect Europe/Paris
 *  list($htmlform, $js) = templateTZform('Europe/Paris');
 *
 * @param string $preselected_timezone preselected timezone (optional)
 *
 * @return an array containing the generated HTML form and Javascript code
 **/
function generateTimeZoneForm($preselected_timezone='')
{
    // Select the first available timezone if no preselected value is passed
    if ($preselected_timezone == '') {
        $l = timezone_identifiers_list();
        $preselected_timezone = $l[0];
    }

    // Try to split the provided timezone
    $spos = strpos($preselected_timezone, '/');
    $pcontinent = substr($preselected_timezone, 0, $spos);
    $pcity = substr($preselected_timezone, $spos+1);

    // Display config form:
    $timezone_form = '';
    $timezone_js = '';

    // The list is in the form 'Europe/Paris', 'America/Argentina/Buenos_Aires'...
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

    $continents_html = '';
    $continents = array_keys($continents);

    foreach ($continents as $continent) {
        $continents_html .= '<option  value="'.$continent.'"';
        if ($pcontinent == $continent) {
            $continents_html .= ' selected="selected"';
        }
        $continents_html .= '>'.$continent.'</option>';
    }

    // Timezone selection form
    $timezone_form = 'Continent:';
    $timezone_form .= '<select name="continent" id="continent" onChange="onChangecontinent();">';
    $timezone_form .= $continents_html.'</select>';
    $timezone_form .= '&nbsp;&nbsp;&nbsp;&nbsp;City:';
    $timezone_form .= '<select name="city" id="city">'.$cities[$pcontinent].'</select><br />';

    // Javascript handler - updates the city list when the user selects a continent
    $timezone_js = '<script>';
    $timezone_js .= 'function onChangecontinent() {';
    $timezone_js .= 'document.getElementById("city").innerHTML =';
    $timezone_js .= ' citiescontinent[document.getElementById("continent").value]; }';
    $timezone_js .= 'var citiescontinent = '.json_encode($cities).';';
    $timezone_js .= '</script>';

    return array($timezone_form, $timezone_js);
}

/**
 * Tells if a continent/city pair form a valid timezone
 *
 * Note: 'UTC/UTC' is mapped to 'UTC'
 *
 * @param string $continent the timezone continent
 * @param string $city      the timezone city
 *
 * @return whether continent/city is a valid timezone
 */
function isTimeZoneValid($continent, $city)
{
    if ($continent == 'UTC' && $city == 'UTC') {
        return true;
    }

    return in_array(
        $continent.'/'.$city,
        timezone_identifiers_list()
    );
}
?>
