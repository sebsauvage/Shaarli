<?php

/**
 * This class is in charge of building the final page.
 * (This is basically a wrapper around RainTPL which pre-fills some fields.)
 * $p = new PageBuilder();
 * $p->assign('myfield','myvalue');
 * $p->renderPage('mytemplate');
 */
class PageBuilder
{
    /**
     * @var RainTPL RainTPL instance.
     */
    private $tpl;

    /**
     * PageBuilder constructor.
     * $tpl is initialized at false for lazy loading.
     */
    function __construct()
    {
        $this->tpl = false;
    }

    /**
     * Initialize all default tpl tags.
     */
    private function initialize()
    {
        $this->tpl = new RainTPL();
        $conf = ConfigManager::getInstance();

        try {
            $version = ApplicationUtils::checkUpdate(
                shaarli_version,
                $conf->get('config.UPDATECHECK_FILENAME'),
                $conf->get('config.UPDATECHECK_INTERVAL'),
                $conf->get('config.ENABLE_UPDATECHECK'),
                isLoggedIn(),
                $conf->get('config.UPDATECHECK_BRANCH')
            );
            $this->tpl->assign('newVersion', escape($version));
            $this->tpl->assign('versionError', '');

        } catch (Exception $exc) {
            logm($conf->get('config.LOG_FILE'), $_SERVER['REMOTE_ADDR'], $exc->getMessage());
            $this->tpl->assign('newVersion', '');
            $this->tpl->assign('versionError', escape($exc->getMessage()));
        }

        $this->tpl->assign('feedurl', escape(index_url($_SERVER)));
        $searchcrits = ''; // Search criteria
        if (!empty($_GET['searchtags'])) {
            $searchcrits .= '&searchtags=' . urlencode($_GET['searchtags']);
        }
        if (!empty($_GET['searchterm'])) {
            $searchcrits .= '&searchterm=' . urlencode($_GET['searchterm']);
        }
        $this->tpl->assign('searchcrits', $searchcrits);
        $this->tpl->assign('source', index_url($_SERVER));
        $this->tpl->assign('version', shaarli_version);
        $this->tpl->assign('scripturl', index_url($_SERVER));
        $this->tpl->assign('pagetitle', 'Shaarli');
        $this->tpl->assign('privateonly', !empty($_SESSION['privateonly'])); // Show only private links?
        if ($conf->exists('title')) {
            $this->tpl->assign('pagetitle', $conf->get('title'));
        }
        if ($conf->exists('titleLink')) {
            $this->tpl->assign('titleLink', $conf->get('titleLink'));
        }
        if ($conf->exists('pagetitle')) {
            $this->tpl->assign('pagetitle', $conf->get('pagetitle'));
        }
        $this->tpl->assign('shaarlititle', $conf->get('title', 'Shaarli'));
        $this->tpl->assign('openshaarli', $conf->get('config.OPEN_SHAARLI', false));
        $this->tpl->assign('showatom', $conf->get('config.SHOW_ATOM', false));
        $this->tpl->assign('hide_timestamps', $conf->get('config.HIDE_TIMESTAMPS', false));
        // FIXME! Globals
        if (!empty($GLOBALS['plugin_errors'])) {
            $this->tpl->assign('plugin_errors', $GLOBALS['plugin_errors']);
        }
    }

    /**
     * The following assign() method is basically the same as RainTPL (except lazy loading)
     *
     * @param string $placeholder Template placeholder.
     * @param mixed  $value       Value to assign.
     */
    public function assign($placeholder, $value)
    {
        // Lazy initialization
        if ($this->tpl === false) {
            $this->initialize();
        }
        $this->tpl->assign($placeholder, $value);
    }

    /**
     * Assign an array of data to the template builder.
     *
     * @param array $data Data to assign.
     *
     * @return false if invalid data.
     */
    public function assignAll($data)
    {
        // Lazy initialization
        if ($this->tpl === false) {
            $this->initialize();
        }

        if (empty($data) || !is_array($data)){
            return false;
        }

        foreach ($data as $key => $value) {
            $this->assign($key, $value);
        }
    }

    /**
     * Render a specific page (using a template file).
     * e.g. $pb->renderPage('picwall');
     *
     * @param string $page Template filename (without extension).
     */
    public function renderPage($page)
    {
        // Lazy initialization
        if ($this->tpl===false) {
            $this->initialize();
        }
        $this->tpl->draw($page);
    }

    /**
     * Render a 404 page (uses the template : tpl/404.tpl)
     * usage : $PAGE->render404('The link was deleted')
     *
     * @param string $message A messate to display what is not found
     */
    public function render404($message = 'The page you are trying to reach does not exist or has been deleted.')
    {
        header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
        $this->tpl->assign('error_message', $message);
        $this->renderPage('404');
    }
}
