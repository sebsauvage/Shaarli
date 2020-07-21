<?php
/**
 * Shaarli - The personal, minimalist, super-fast, database free, bookmarking service.
 *
 * Friendly fork by the Shaarli community:
 *  - https://github.com/shaarli/Shaarli
 *
 * Original project by sebsauvage.net:
 *  - http://sebsauvage.net/wiki/doku.php?id=php:shaarli
 *  - https://github.com/sebsauvage/Shaarli
 *
 * Licence: http://www.opensource.org/licenses/zlib-license.php
 */

// Set 'UTC' as the default timezone if it is not defined in php.ini
// See http://php.net/manual/en/datetime.configuration.php#ini.date.timezone
if (date_default_timezone_get() == '') {
    date_default_timezone_set('UTC');
}

/*
 * PHP configuration
 */

// http://server.com/x/shaarli --> /shaarli/
define('WEB_PATH', substr($_SERVER['REQUEST_URI'], 0, 1+strrpos($_SERVER['REQUEST_URI'], '/', 0)));

// High execution time in case of problematic imports/exports.
ini_set('max_input_time', '60');

// Try to set max upload file size and read
ini_set('memory_limit', '128M');
ini_set('post_max_size', '16M');
ini_set('upload_max_filesize', '16M');

// See all error except warnings
error_reporting(E_ALL^E_WARNING);

// 3rd-party libraries
if (! file_exists(__DIR__ . '/vendor/autoload.php')) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "Error: missing Composer configuration\n\n"
        ."If you installed Shaarli through Git or using the development branch,\n"
        ."please refer to the installation documentation to install PHP"
        ." dependencies using Composer:\n"
        ."- https://shaarli.readthedocs.io/en/master/Server-configuration/\n"
        ."- https://shaarli.readthedocs.io/en/master/Download-and-Installation/";
    exit;
}
require_once 'inc/rain.tpl.class.php';
require_once __DIR__ . '/vendor/autoload.php';

// Shaarli library
require_once 'application/bookmark/LinkUtils.php';
require_once 'application/config/ConfigPlugin.php';
require_once 'application/http/HttpUtils.php';
require_once 'application/http/UrlUtils.php';
require_once 'application/updater/UpdaterUtils.php';
require_once 'application/FileUtils.php';
require_once 'application/TimeZone.php';
require_once 'application/Utils.php';

use Shaarli\ApplicationUtils;
use Shaarli\Config\ConfigManager;
use Shaarli\Container\ContainerBuilder;
use Shaarli\Languages;
use Shaarli\Plugin\PluginManager;
use Shaarli\Security\CookieManager;
use Shaarli\Security\LoginManager;
use Shaarli\Security\SessionManager;
use Slim\App;

// Ensure the PHP version is supported
try {
    ApplicationUtils::checkPHPVersion('7.1', PHP_VERSION);
} catch (Exception $exc) {
    header('Content-Type: text/plain; charset=utf-8');
    echo $exc->getMessage();
    exit;
}

define('SHAARLI_VERSION', ApplicationUtils::getVersion(__DIR__ .'/'. ApplicationUtils::$VERSION_FILE));

// Force cookie path (but do not change lifetime)
$cookie = session_get_cookie_params();
$cookiedir = '';
if (dirname($_SERVER['SCRIPT_NAME']) != '/') {
    $cookiedir = dirname($_SERVER["SCRIPT_NAME"]).'/';
}
// Set default cookie expiration and path.
session_set_cookie_params($cookie['lifetime'], $cookiedir, $_SERVER['SERVER_NAME']);
// Set session parameters on server side.
// Use cookies to store session.
ini_set('session.use_cookies', 1);
// Force cookies for session (phpsessionID forbidden in URL).
ini_set('session.use_only_cookies', 1);
// Prevent PHP form using sessionID in URL if cookies are disabled.
ini_set('session.use_trans_sid', false);

session_name('shaarli');
// Start session if needed (Some server auto-start sessions).
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Regenerate session ID if invalid or not defined in cookie.
if (isset($_COOKIE['shaarli']) && !SessionManager::checkId($_COOKIE['shaarli'])) {
    session_regenerate_id(true);
    $_COOKIE['shaarli'] = session_id();
}

$conf = new ConfigManager();

// In dev mode, throw exception on any warning
if ($conf->get('dev.debug', false)) {
    // See all errors (for debugging only)
    error_reporting(-1);

    set_error_handler(function ($errno, $errstr, $errfile, $errline, array $errcontext) {
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    });
}

$sessionManager = new SessionManager($_SESSION, $conf, session_save_path());
$cookieManager = new CookieManager($_COOKIE);
$loginManager = new LoginManager($conf, $sessionManager, $cookieManager);
$loginManager->generateStaySignedInToken($_SERVER['REMOTE_ADDR']);
$clientIpId = client_ip_id($_SERVER);

// LC_MESSAGES isn't defined without php-intl, in this case use LC_COLLATE locale instead.
if (! defined('LC_MESSAGES')) {
    define('LC_MESSAGES', LC_COLLATE);
}

// Sniff browser language and set date format accordingly.
if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
    autoLocale($_SERVER['HTTP_ACCEPT_LANGUAGE']);
}

new Languages(setlocale(LC_MESSAGES, 0), $conf);

$conf->setEmpty('general.timezone', date_default_timezone_get());
$conf->setEmpty('general.title', t('Shared bookmarks on '). escape(index_url($_SERVER)));
RainTPL::$tpl_dir = $conf->get('resource.raintpl_tpl').'/'.$conf->get('resource.theme').'/'; // template directory
RainTPL::$cache_dir = $conf->get('resource.raintpl_tmp'); // cache directory

$pluginManager = new PluginManager($conf);
$pluginManager->load($conf->get('general.enabled_plugins'));

date_default_timezone_set($conf->get('general.timezone', 'UTC'));

ob_start();  // Output buffering for the page cache.

// Prevent caching on client side or proxy: (yes, it's ugly)
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$loginManager->checkLoginState($clientIpId);

// ------------------------------------------------------------------------------------------
// Token management for XSRF protection
// Token should be used in any form which acts on data (create,update,delete,import...).
if (!isset($_SESSION['tokens'])) {
    $_SESSION['tokens']=array();  // Token are attached to the session.
}

if (!isset($_SESSION['LINKS_PER_PAGE'])) {
    $_SESSION['LINKS_PER_PAGE'] = $conf->get('general.links_per_page', 20);
}

$containerBuilder = new ContainerBuilder($conf, $sessionManager, $cookieManager, $loginManager);
$container = $containerBuilder->build();
$app = new App($container);

// REST API routes
$app->group('/api/v1', function () {
    $this->get('/info', '\Shaarli\Api\Controllers\Info:getInfo')->setName('getInfo');
    $this->get('/links', '\Shaarli\Api\Controllers\Links:getLinks')->setName('getLinks');
    $this->get('/links/{id:[\d]+}', '\Shaarli\Api\Controllers\Links:getLink')->setName('getLink');
    $this->post('/links', '\Shaarli\Api\Controllers\Links:postLink')->setName('postLink');
    $this->put('/links/{id:[\d]+}', '\Shaarli\Api\Controllers\Links:putLink')->setName('putLink');
    $this->delete('/links/{id:[\d]+}', '\Shaarli\Api\Controllers\Links:deleteLink')->setName('deleteLink');

    $this->get('/tags', '\Shaarli\Api\Controllers\Tags:getTags')->setName('getTags');
    $this->get('/tags/{tagName:[\w]+}', '\Shaarli\Api\Controllers\Tags:getTag')->setName('getTag');
    $this->put('/tags/{tagName:[\w]+}', '\Shaarli\Api\Controllers\Tags:putTag')->setName('putTag');
    $this->delete('/tags/{tagName:[\w]+}', '\Shaarli\Api\Controllers\Tags:deleteTag')->setName('deleteTag');

    $this->get('/history', '\Shaarli\Api\Controllers\HistoryController:getHistory')->setName('getHistory');
})->add('\Shaarli\Api\ApiMiddleware');

$app->group('', function () {
    $this->get('/install', '\Shaarli\Front\Controller\Visitor\InstallController:index')->setName('displayInstall');
    $this->get('/install/session-test', '\Shaarli\Front\Controller\Visitor\InstallController:sessionTest');
    $this->post('/install', '\Shaarli\Front\Controller\Visitor\InstallController:save')->setName('saveInstall');

    /* -- PUBLIC --*/
    $this->get('/', '\Shaarli\Front\Controller\Visitor\BookmarkListController:index');
    $this->get('/shaare/{hash}', '\Shaarli\Front\Controller\Visitor\BookmarkListController:permalink');
    $this->get('/login', '\Shaarli\Front\Controller\Visitor\LoginController:index')->setName('login');
    $this->post('/login', '\Shaarli\Front\Controller\Visitor\LoginController:login')->setName('processLogin');
    $this->get('/picture-wall', '\Shaarli\Front\Controller\Visitor\PictureWallController:index');
    $this->get('/tags/cloud', '\Shaarli\Front\Controller\Visitor\TagCloudController:cloud');
    $this->get('/tags/list', '\Shaarli\Front\Controller\Visitor\TagCloudController:list');
    $this->get('/daily', '\Shaarli\Front\Controller\Visitor\DailyController:index');
    $this->get('/daily-rss', '\Shaarli\Front\Controller\Visitor\DailyController:rss')->setName('rss');
    $this->get('/feed/atom', '\Shaarli\Front\Controller\Visitor\FeedController:atom')->setName('atom');
    $this->get('/feed/rss', '\Shaarli\Front\Controller\Visitor\FeedController:rss');
    $this->get('/open-search', '\Shaarli\Front\Controller\Visitor\OpenSearchController:index');

    $this->get('/add-tag/{newTag}', '\Shaarli\Front\Controller\Visitor\TagController:addTag');
    $this->get('/remove-tag/{tag}', '\Shaarli\Front\Controller\Visitor\TagController:removeTag');

    /* -- LOGGED IN -- */
    $this->get('/logout', '\Shaarli\Front\Controller\Admin\LogoutController:index');
    $this->get('/admin/tools', '\Shaarli\Front\Controller\Admin\ToolsController:index');
    $this->get('/admin/password', '\Shaarli\Front\Controller\Admin\PasswordController:index');
    $this->post('/admin/password', '\Shaarli\Front\Controller\Admin\PasswordController:change');
    $this->get('/admin/configure', '\Shaarli\Front\Controller\Admin\ConfigureController:index');
    $this->post('/admin/configure', '\Shaarli\Front\Controller\Admin\ConfigureController:save');
    $this->get('/admin/tags', '\Shaarli\Front\Controller\Admin\ManageTagController:index');
    $this->post('/admin/tags', '\Shaarli\Front\Controller\Admin\ManageTagController:save');
    $this->get('/admin/add-shaare', '\Shaarli\Front\Controller\Admin\ManageShaareController:addShaare');
    $this->get('/admin/shaare', '\Shaarli\Front\Controller\Admin\ManageShaareController:displayCreateForm');
    $this->get('/admin/shaare/{id:[0-9]+}', '\Shaarli\Front\Controller\Admin\ManageShaareController:displayEditForm');
    $this->post('/admin/shaare', '\Shaarli\Front\Controller\Admin\ManageShaareController:save');
    $this->get('/admin/shaare/delete', '\Shaarli\Front\Controller\Admin\ManageShaareController:deleteBookmark');
    $this->get('/admin/shaare/visibility', '\Shaarli\Front\Controller\Admin\ManageShaareController:changeVisibility');
    $this->get('/admin/shaare/{id:[0-9]+}/pin', '\Shaarli\Front\Controller\Admin\ManageShaareController:pinBookmark');
    $this->patch(
        '/admin/shaare/{id:[0-9]+}/update-thumbnail',
        '\Shaarli\Front\Controller\Admin\ThumbnailsController:ajaxUpdate'
    );
    $this->get('/admin/export', '\Shaarli\Front\Controller\Admin\ExportController:index');
    $this->post('/admin/export', '\Shaarli\Front\Controller\Admin\ExportController:export');
    $this->get('/admin/import', '\Shaarli\Front\Controller\Admin\ImportController:index');
    $this->post('/admin/import', '\Shaarli\Front\Controller\Admin\ImportController:import');
    $this->get('/admin/plugins', '\Shaarli\Front\Controller\Admin\PluginsController:index');
    $this->post('/admin/plugins', '\Shaarli\Front\Controller\Admin\PluginsController:save');
    $this->get('/admin/token', '\Shaarli\Front\Controller\Admin\TokenController:getToken');
    $this->get('/admin/thumbnails', '\Shaarli\Front\Controller\Admin\ThumbnailsController:index');

    $this->get('/links-per-page', '\Shaarli\Front\Controller\Admin\SessionFilterController:linksPerPage');
    $this->get('/visibility/{visibility}', '\Shaarli\Front\Controller\Admin\SessionFilterController:visibility');
    $this->get('/untagged-only', '\Shaarli\Front\Controller\Admin\SessionFilterController:untaggedOnly');
})->add('\Shaarli\Front\ShaarliMiddleware');

$response = $app->run(true);

$app->respond($response);
