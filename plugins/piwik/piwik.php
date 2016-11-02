<?php
/**
 * Piwik plugin.
 * Adds tracking code on each page.
 */

/**
 * Initialization function.
 * It will be called when the plugin is loaded.
 * This function can be used to return a list of initialization errors.
 *
 * @param $conf ConfigManager instance.
 *
 * @return array List of errors (optional).
 */
function piwik_init($conf)
{
    $piwikUrl = $conf->get('plugins.PIWIK_URL');
    $piwikSiteid = $conf->get('plugins.PIWIK_SITEID');
    if (empty($piwikUrl) || empty($piwikSiteid)) {
        $error = 'Piwik plugin error: ' .
            'Please define PIWIK_URL and PIWIK_SITEID in the plugin administration page.';
        return array($error);
    }
}

/**
 * Hook render_footer.
 * Executed on every page redering.
 *
 * Template placeholders:
 *   - text
 *   - endofpage
 *   - js_files
 *
 * Data:
 *   - _PAGE_: current page
 *   - _LOGGEDIN_: true/false
 *
 * @param array $data data passed to plugin
 *
 * @return array altered $data.
 */
function hook_piwik_render_footer($data, $conf)
{
    $piwikUrl = $conf->get('plugins.PIWIK_URL');
    $piwikSiteid = $conf->get('plugins.PIWIK_SITEID');
    if (empty($piwikUrl) || empty($piwikSiteid)) {
        return $data;
    }

    // Free elements at the end of the page.
    $data['endofpage'][] = '<!-- Piwik -->' .
'<script type="text/javascript">' .
'  var _paq = _paq || [];' .
'  _paq.push([\'trackPageView\']);' .
'  _paq.push([\'enableLinkTracking\']);' .
'  (function() {' .
'    var u="//' . $piwikUrl . '/";' .
'    _paq.push([\'setTrackerUrl\', u+\'piwik.php\']);' .
'    _paq.push([\'setSiteId\', \'' . $piwikSiteid . '\']);' .
'    var d=document, g=d.createElement(\'script\'), s=d.getElementsByTagName(\'script\')[0];' .
'    g.type=\'text/javascript\'; g.async=true; g.defer=true; g.src=u+\'piwik.js\'; s.parentNode.insertBefore(g,s);' .
'  })();' .
'</script>' .
'<noscript><p><img src="//' . $piwikUrl . '/piwik.php?idsite=' . $piwikSiteid . '" style="border:0;" alt="" /></p></noscript>' .
'<!-- End Piwik Code -->';

    return $data;
}

