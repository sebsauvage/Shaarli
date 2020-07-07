<?php

namespace Shaarli\Front;

use Shaarli\Container\ShaarliContainer;
use Shaarli\Front\Exception\ShaarliFrontException;
use Shaarli\Front\Exception\UnauthorizedException;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class ShaarliMiddleware
 *
 * This will be called before accessing any Shaarli controller.
 */
class ShaarliMiddleware
{
    /** @var ShaarliContainer contains all Shaarli DI */
    protected $container;

    public function __construct(ShaarliContainer $container)
    {
        $this->container = $container;
    }

    /**
     * Middleware execution:
     *   - run updates
     *   - if not logged in open shaarli, redirect to login
     *   - execute the controller
     *   - return the response
     *
     * In case of error, the error template will be displayed with the exception message.
     *
     * @param  Request  $request  Slim request
     * @param  Response $response Slim response
     * @param  callable $next     Next action
     *
     * @return Response response.
     */
    public function __invoke(Request $request, Response $response, callable $next): Response
    {
        $this->container->basePath = rtrim($request->getUri()->getBasePath(), '/');

        try {
            if (!is_file($this->container->conf->getConfigFileExt())
                && !in_array($next->getName(), ['displayInstall', 'saveInstall'], true)
            ) {
                return $response->withRedirect($this->container->basePath . '/install');
            }

            $this->runUpdates();
            $this->checkOpenShaarli($request, $response, $next);

            return $next($request, $response);
        } catch (ShaarliFrontException $e) {
            // Possible functional error
            $this->container->pageBuilder->reset();
            $this->container->pageBuilder->assign('message', $e->getMessage());

            $response = $response->withStatus($e->getCode());

            return $response->write($this->container->pageBuilder->render('error'));
        } catch (UnauthorizedException $e) {
            return $response->withRedirect($this->container->basePath . '/login');
        } catch (\Throwable $e) {
            // Unknown error encountered
            $this->container->pageBuilder->reset();
            if ($this->container->conf->get('dev.debug', false)) {
                $this->container->pageBuilder->assign('message', $e->getMessage());
                $this->container->pageBuilder->assign(
                    'stacktrace',
                    nl2br(get_class($e) .': '. PHP_EOL . $e->getTraceAsString())
                );
            } else {
                $this->container->pageBuilder->assign('message', t('An unexpected error occurred.'));
            }

            $response = $response->withStatus(500);

            return $response->write($this->container->pageBuilder->render('error'));
        }
    }

    /**
     * Run the updater for every requests processed while logged in.
     */
    protected function runUpdates(): void
    {
        if ($this->container->loginManager->isLoggedIn() !== true) {
            return;
        }

        $newUpdates = $this->container->updater->update();
        if (!empty($newUpdates)) {
            $this->container->updater->writeUpdates(
                $this->container->conf->get('resource.updates'),
                $this->container->updater->getDoneUpdates()
            );

            $this->container->pageCacheManager->invalidateCaches();
        }
    }

    /**
     * Access is denied to most pages with `hide_public_links` + `force_login` settings.
     */
    protected function checkOpenShaarli(Request $request, Response $response, callable $next): bool
    {
        if (// if the user isn't logged in
            !$this->container->loginManager->isLoggedIn()
            // and Shaarli doesn't have public content...
            && $this->container->conf->get('privacy.hide_public_links')
            // and is configured to enforce the login
            && $this->container->conf->get('privacy.force_login')
            // and the current page isn't already the login page
            // and the user is not requesting a feed (which would lead to a different content-type as expected)
            && !in_array($next->getName(), ['login', 'atom', 'rss'], true)
        ) {
            throw new UnauthorizedException();
        }

        return true;
    }
}
