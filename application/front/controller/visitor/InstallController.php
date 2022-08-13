<?php

declare(strict_types=1);

namespace Shaarli\Front\Controller\Visitor;

use Shaarli\Container\ShaarliContainer;
use Shaarli\Front\Exception\AlreadyInstalledException;
use Shaarli\Front\Exception\ResourcePermissionException;
use Shaarli\Helper\ApplicationUtils;
use Shaarli\Languages;
use Shaarli\Security\SessionManager;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Slim controller used to render install page, and create initial configuration file.
 */
class InstallController extends ShaarliVisitorController
{
    public const SESSION_TEST_KEY = 'session_tested';
    public const SESSION_TEST_VALUE = 'Working';

    public function __construct(ShaarliContainer $container)
    {
        parent::__construct($container);

        if (is_file($this->container->conf->getConfigFileExt())) {
            throw new AlreadyInstalledException();
        }
    }

    /**
     * Display the install template page.
     * Also test file permissions and sessions beforehand.
     */
    public function index(Request $request, Response $response): Response
    {
        // Before installation, we'll make sure that permissions are set properly, and sessions are working.
        $this->checkPermissions();

        if (
            static::SESSION_TEST_VALUE
            !== $this->container->sessionManager->getSessionParameter(static::SESSION_TEST_KEY)
        ) {
            $this->container->sessionManager->setSessionParameter(static::SESSION_TEST_KEY, static::SESSION_TEST_VALUE);

            return $this->redirect($response, '/install/session-test');
        }

        [$continents, $cities] = generateTimeZoneData(timezone_identifiers_list(), date_default_timezone_get());

        $this->assignView('continents', $continents);
        $this->assignView('cities', $cities);
        $this->assignView('languages', Languages::getAvailableLanguages());

        $phpEol = new \DateTimeImmutable(ApplicationUtils::getPhpEol(PHP_VERSION));

        $permissions = array_merge(
            ApplicationUtils::checkResourcePermissions($this->container->conf),
            ApplicationUtils::checkDatastoreMutex()
        );

        $this->assignView('php_version', PHP_VERSION);
        $this->assignView('php_eol', format_date($phpEol, false));
        $this->assignView('php_has_reached_eol', $phpEol < new \DateTimeImmutable());
        $this->assignView('php_extensions', ApplicationUtils::getPhpExtensionsRequirement());
        $this->assignView('permissions', $permissions);

        $this->assignView('pagetitle', t('Install Shaarli'));

        return $response->write($this->render('install'));
    }

    /**
     * Route checking that the session parameter has been properly saved between two distinct requests.
     * If the session parameter is preserved, redirect to install template page, otherwise displays error.
     */
    public function sessionTest(Request $request, Response $response): Response
    {
        // This part makes sure sessions works correctly.
        // (Because on some hosts, session.save_path may not be set correctly,
        // or we may not have write access to it.)
        if (
            static::SESSION_TEST_VALUE
            !== $this->container->sessionManager->getSessionParameter(static::SESSION_TEST_KEY)
        ) {
            // Step 2: Check if data in session is correct.
            $msg = t(
                '<pre>Sessions do not seem to work correctly on your server.<br>' .
                'Make sure the variable "session.save_path" is set correctly in your PHP config, ' .
                'and that you have write access to it.<br>' .
                'It currently points to %s.<br>' .
                'On some browsers, accessing your server via a hostname like \'localhost\' ' .
                'or any custom hostname without a dot causes cookie storage to fail. ' .
                'We recommend accessing your server via it\'s IP address or Fully Qualified Domain Name.<br>'
            );
            $msg = sprintf($msg, $this->container->sessionManager->getSavePath());

            $this->assignView('message', $msg);

            return $response->write($this->render('error'));
        }

        return $this->redirect($response, '/install');
    }

    /**
     * Save installation form and initialize config file and datastore if necessary.
     */
    public function save(Request $request, Response $response): Response
    {
        $timezone = 'UTC';
        if (
            !empty($request->getParam('continent'))
            && !empty($request->getParam('city'))
            && isTimeZoneValid($request->getParam('continent'), $request->getParam('city'))
        ) {
            $timezone = $request->getParam('continent') . '/' . $request->getParam('city');
        }
        $this->container->conf->set('general.timezone', $timezone);

        $login = $request->getParam('setlogin');
        $this->container->conf->set('credentials.login', $login);
        $salt = sha1(uniqid('', true) . '_' . mt_rand());
        $this->container->conf->set('credentials.salt', $salt);
        $this->container->conf->set('credentials.hash', sha1($request->getParam('setpassword') . $login . $salt));

        if (!empty($request->getParam('title'))) {
            $this->container->conf->set('general.title', escape($request->getParam('title')));
        } else {
            $this->container->conf->set(
                'general.title',
                t('Shared Bookmarks')
            );
        }

        $this->container->conf->set('translation.language', escape($request->getParam('language')));
        $this->container->conf->set('updates.check_updates', !empty($request->getParam('updateCheck')));
        $this->container->conf->set('api.enabled', !empty($request->getParam('enableApi')));
        $this->container->conf->set(
            'api.secret',
            generate_api_secret(
                $this->container->conf->get('credentials.login'),
                $this->container->conf->get('credentials.salt')
            )
        );
        $this->container->conf->set('general.header_link', $this->container->basePath . '/');

        try {
            // Everything is ok, let's create config file.
            $this->container->conf->write($this->container->loginManager->isLoggedIn());
        } catch (\Exception $e) {
            $this->assignView('message', t('Error while writing config file after configuration update.'));
            $this->assignView('stacktrace', $e->getMessage() . PHP_EOL . $e->getTraceAsString());

            return $response->write($this->render('error'));
        }

        $this->container->sessionManager->setSessionParameter(
            SessionManager::KEY_SUCCESS_MESSAGES,
            [t('Shaarli is now configured. Please login and start shaaring your bookmarks!')]
        );

        return $this->redirect($response, '/login');
    }

    protected function checkPermissions(): bool
    {
        // Ensure Shaarli has proper access to its resources
        $errors = ApplicationUtils::checkResourcePermissions($this->container->conf, true);
        if (empty($errors)) {
            return true;
        }

        $message = t('Insufficient permissions:') . PHP_EOL;
        foreach ($errors as $error) {
            $message .= PHP_EOL . $error;
        }

        throw new ResourcePermissionException($message);
    }
}
