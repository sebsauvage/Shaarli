<?php
namespace Shaarli;

use Exception;
use Shaarli\Config\ConfigManager;

/**
 * Shaarli (application) utilities
 */
class ApplicationUtils
{
    /**
     * @var string File containing the current version
     */
    public static $VERSION_FILE = 'shaarli_version.php';

    private static $GIT_URL = 'https://raw.githubusercontent.com/shaarli/Shaarli';
    private static $GIT_BRANCHES = array('latest', 'stable');
    private static $VERSION_START_TAG = '<?php /* ';
    private static $VERSION_END_TAG = ' */ ?>';

    /**
     * Gets the latest version code from the Git repository
     *
     * The code is read from the raw content of the version file on the Git server.
     *
     * @param string $url     URL to reach to get the latest version.
     * @param int    $timeout Timeout to check the URL (in seconds).
     *
     * @return mixed the version code from the repository if available, else 'false'
     */
    public static function getLatestGitVersionCode($url, $timeout = 2)
    {
        list($headers, $data) = get_http_response($url, $timeout);

        if (strpos($headers[0], '200 OK') === false) {
            error_log('Failed to retrieve ' . $url);
            return false;
        }

        return $data;
    }

    /**
     * Retrieve the version from a remote URL or a file.
     *
     * @param string $remote  URL or file to fetch.
     * @param int    $timeout For URLs fetching.
     *
     * @return bool|string The version or false if it couldn't be retrieved.
     */
    public static function getVersion($remote, $timeout = 2)
    {
        if (startsWith($remote, 'http')) {
            if (($data = static::getLatestGitVersionCode($remote, $timeout)) === false) {
                return false;
            }
        } else {
            if (!is_file($remote)) {
                return false;
            }
            $data = file_get_contents($remote);
        }

        return str_replace(
            array(self::$VERSION_START_TAG, self::$VERSION_END_TAG, PHP_EOL),
            array('', '', ''),
            $data
        );
    }

    /**
     * Checks if a new Shaarli version has been published on the Git repository
     *
     * Updates checks are run periodically, according to the following criteria:
     * - the update checks are enabled (install, global config);
     * - the user is logged in (or this is an open instance);
     * - the last check is older than a given interval;
     * - the check is non-blocking if the HTTPS connection to Git fails;
     * - in case of failure, the update file's modification date is updated,
     *   to avoid intempestive connection attempts.
     *
     * @param string $currentVersion the current version code
     * @param string $updateFile     the file where to store the latest version code
     * @param int    $checkInterval  the minimum interval between update checks (in seconds
     * @param bool   $enableCheck    whether to check for new versions
     * @param bool   $isLoggedIn     whether the user is logged in
     * @param string $branch         check update for the given branch
     *
     * @throws Exception an invalid branch has been set for update checks
     *
     * @return mixed the new version code if available and greater, else 'false'
     */
    public static function checkUpdate(
        $currentVersion,
        $updateFile,
        $checkInterval,
        $enableCheck,
        $isLoggedIn,
        $branch = 'stable'
    ) {
        // Do not check versions for visitors
        // Do not check if the user doesn't want to
        // Do not check with dev version
        if (!$isLoggedIn || empty($enableCheck) || $currentVersion === 'dev') {
            return false;
        }

        if (is_file($updateFile) && (filemtime($updateFile) > time() - $checkInterval)) {
            // Shaarli has checked for updates recently - skip HTTP query
            $latestKnownVersion = file_get_contents($updateFile);

            if (version_compare($latestKnownVersion, $currentVersion) == 1) {
                return $latestKnownVersion;
            }
            return false;
        }

        if (!in_array($branch, self::$GIT_BRANCHES)) {
            throw new Exception(
                'Invalid branch selected for updates: "' . $branch . '"'
            );
        }

        // Late Static Binding allows overriding within tests
        // See http://php.net/manual/en/language.oop5.late-static-bindings.php
        $latestVersion = static::getVersion(
            self::$GIT_URL . '/' . $branch . '/' . self::$VERSION_FILE
        );

        if (!$latestVersion) {
            // Only update the file's modification date
            file_put_contents($updateFile, $currentVersion);
            return false;
        }

        // Update the file's content and modification date
        file_put_contents($updateFile, $latestVersion);

        if (version_compare($latestVersion, $currentVersion) == 1) {
            return $latestVersion;
        }

        return false;
    }

    /**
     * Checks the PHP version to ensure Shaarli can run
     *
     * @param string $minVersion minimum PHP required version
     * @param string $curVersion current PHP version (use PHP_VERSION)
     *
     * @throws Exception the PHP version is not supported
     */
    public static function checkPHPVersion($minVersion, $curVersion)
    {
        if (version_compare($curVersion, $minVersion) < 0) {
            $msg = t(
                'Your PHP version is obsolete!'
                . ' Shaarli requires at least PHP %s, and thus cannot run.'
                . ' Your PHP version has known security vulnerabilities and should be'
                . ' updated as soon as possible.'
            );
            throw new Exception(sprintf($msg, $minVersion));
        }
    }

    /**
     * Checks Shaarli has the proper access permissions to its resources
     *
     * @param ConfigManager $conf Configuration Manager instance.
     *
     * @return array A list of the detected configuration issues
     */
    public static function checkResourcePermissions($conf)
    {
        $errors = array();
        $rainTplDir = rtrim($conf->get('resource.raintpl_tpl'), '/');

        // Check script and template directories are readable
        foreach (array(
                     'application',
                     'inc',
                     'plugins',
                     $rainTplDir,
                     $rainTplDir . '/' . $conf->get('resource.theme'),
                 ) as $path) {
            if (!is_readable(realpath($path))) {
                $errors[] = '"' . $path . '" ' . t('directory is not readable');
            }
        }

        // Check cache and data directories are readable and writable
        foreach (array(
                     $conf->get('resource.thumbnails_cache'),
                     $conf->get('resource.data_dir'),
                     $conf->get('resource.page_cache'),
                     $conf->get('resource.raintpl_tmp'),
                 ) as $path) {
            if (!is_readable(realpath($path))) {
                $errors[] = '"' . $path . '" ' . t('directory is not readable');
            }
            if (!is_writable(realpath($path))) {
                $errors[] = '"' . $path . '" ' . t('directory is not writable');
            }
        }

        // Check configuration files are readable and writable
        foreach (array(
                     $conf->getConfigFileExt(),
                     $conf->get('resource.datastore'),
                     $conf->get('resource.ban_file'),
                     $conf->get('resource.log'),
                     $conf->get('resource.update_check'),
                 ) as $path) {
            if (!is_file(realpath($path))) {
                # the file may not exist yet
                continue;
            }

            if (!is_readable(realpath($path))) {
                $errors[] = '"' . $path . '" ' . t('file is not readable');
            }
            if (!is_writable(realpath($path))) {
                $errors[] = '"' . $path . '" ' . t('file is not writable');
            }
        }

        return $errors;
    }

    /**
     * Returns a salted hash representing the current Shaarli version.
     *
     * Useful for assets browser cache.
     *
     * @param string $currentVersion of Shaarli
     * @param string $salt           User personal salt, also used for the authentication
     *
     * @return string version hash
     */
    public static function getVersionHash($currentVersion, $salt)
    {
        return hash_hmac('sha256', $currentVersion, $salt);
    }
}
