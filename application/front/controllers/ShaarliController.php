<?php

declare(strict_types=1);

namespace Shaarli\Front\Controller;

use Shaarli\Bookmark\BookmarkFilter;
use Shaarli\Container\ShaarliContainer;

abstract class ShaarliController
{
    /** @var ShaarliContainer */
    protected $ci;

    /** @param ShaarliContainer $ci Slim container (extended for attribute completion). */
    public function __construct(ShaarliContainer $ci)
    {
        $this->ci = $ci;
    }

    /**
     * Assign variables to RainTPL template through the PageBuilder.
     *
     * @param mixed $value Value to assign to the template
     */
    protected function assignView(string $name, $value): self
    {
        $this->ci->pageBuilder->assign($name, $value);

        return $this;
    }

    protected function render(string $template): string
    {
        $this->assignView('linkcount', $this->ci->bookmarkService->count(BookmarkFilter::$ALL));
        $this->assignView('privateLinkcount', $this->ci->bookmarkService->count(BookmarkFilter::$PRIVATE));
        $this->assignView('plugin_errors', $this->ci->pluginManager->getErrors());

        $this->executeDefaultHooks($template);

        return $this->ci->pageBuilder->render($template);
    }

    /**
     * Call plugin hooks for header, footer and includes, specifying which page will be rendered.
     * Then assign generated data to RainTPL.
     */
    protected function executeDefaultHooks(string $template): void
    {
        $common_hooks = [
            'includes',
            'header',
            'footer',
        ];

        foreach ($common_hooks as $name) {
            $plugin_data = [];
            $this->ci->pluginManager->executeHooks(
                'render_' . $name,
                $plugin_data,
                [
                    'target' => $template,
                    'loggedin' => $this->ci->loginManager->isLoggedIn()
                ]
            );
            $this->assignView('plugins_' . $name, $plugin_data);
        }
    }
}
