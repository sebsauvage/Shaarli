<?php

/**
 * Wrapper function for translation which match the API
 * of gettext()/_() and ngettext().
 *
 * Not doing translation for now.
 *
 * @param string $text  Text to translate.
 * @param string $nText The plural message ID.
 * @param int    $nb    The number of items for plural forms.
 *
 * @return String Text translated.
 */
function t($text, $nText = '', $nb = 0) {
    if (empty($nText)) {
        return $text;
    }
    $actualForm = $nb > 1 ? $nText : $text;
    return sprintf($actualForm, $nb);
}
