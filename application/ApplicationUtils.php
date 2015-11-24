<?php
/**
 * Shaarli (application) utilities
 */
class ApplicationUtils
{

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
            throw new Exception(
                'Your PHP version is obsolete!'
                .' Shaarli requires at least PHP '.$minVersion.', and thus cannot run.'
                .' Your PHP version has known security vulnerabilities and should be'
                .' updated as soon as possible.'
            );
        }
    }

    /**
     * Checks Shaarli has the proper access permissions to its resources
     *
     * @param array $globalConfig The $GLOBALS['config'] array
     *
     * @return array A list of the detected configuration issues
     */
    public static function checkResourcePermissions($globalConfig)
    {
        $errors = array();

        // Check script and template directories are readable
        foreach (array(
            'application',
            'inc',
            'plugins',
            $globalConfig['RAINTPL_TPL']
        ) as $path) {
            if (! is_readable(realpath($path))) {
                $errors[] = '"'.$path.'" directory is not readable';
            }
        }

        // Check cache and data directories are readable and writeable
        foreach (array(
            $globalConfig['CACHEDIR'],
            $globalConfig['DATADIR'],
            $globalConfig['PAGECACHE'],
            $globalConfig['RAINTPL_TMP']
        ) as $path) {
            if (! is_readable(realpath($path))) {
                $errors[] = '"'.$path.'" directory is not readable';
            }
            if (! is_writable(realpath($path))) {
                $errors[] = '"'.$path.'" directory is not writable';
            }
        }

        // Check configuration files are readable and writeable
        foreach (array(
            $globalConfig['CONFIG_FILE'],
            $globalConfig['DATASTORE'],
            $globalConfig['IPBANS_FILENAME'],
            $globalConfig['LOG_FILE'],
            $globalConfig['UPDATECHECK_FILENAME']
        ) as $path) {
            if (! is_file(realpath($path))) {
                # the file may not exist yet
                continue;
            }

            if (! is_readable(realpath($path))) {
                $errors[] = '"'.$path.'" file is not readable';
            }
            if (! is_writable(realpath($path))) {
                $errors[] = '"'.$path.'" file is not writable';
            }
        }

        return $errors;
    }
}
