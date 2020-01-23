<?php

declare(strict_types=1);

namespace Shaarli\Front\Controller;

use PHPUnit\Framework\TestCase;
use Shaarli\Bookmark\BookmarkFilter;
use Shaarli\Bookmark\BookmarkServiceInterface;
use Shaarli\Container\ShaarliContainer;
use Shaarli\Plugin\PluginManager;
use Shaarli\Render\PageBuilder;
use Shaarli\Security\LoginManager;

/**
 * Class ShaarliControllerTest
 *
 * This class is used to test default behavior of ShaarliController abstract class.
 * It uses a dummy non abstract controller.
 */
class ShaarliControllerTest extends TestCase
{
    /** @var ShaarliContainer */
    protected $container;

    /** @var LoginController */
    protected $controller;

    /** @var mixed[] List of variable assigned to the template */
    protected $assignedValues;

    public function setUp(): void
    {
        $this->container = $this->createMock(ShaarliContainer::class);
        $this->controller = new class($this->container) extends ShaarliController
        {
            public function assignView(string $key, $value): ShaarliController
            {
                return parent::assignView($key, $value);
            }

            public function render(string $template): string
            {
                return parent::render($template);
            }
        };
        $this->assignedValues = [];
    }

    public function testAssignView(): void
    {
        $this->createValidContainerMockSet();

        $self = $this->controller->assignView('variableName', 'variableValue');

        static::assertInstanceOf(ShaarliController::class, $self);
        static::assertSame('variableValue', $this->assignedValues['variableName']);
    }

    public function testRender(): void
    {
        $this->createValidContainerMockSet();

        $render = $this->controller->render('templateName');

        static::assertSame('templateName', $render);

        static::assertSame(10, $this->assignedValues['linkcount']);
        static::assertSame(5, $this->assignedValues['privateLinkcount']);
        static::assertSame(['error'], $this->assignedValues['plugin_errors']);

        static::assertSame('templateName', $this->assignedValues['plugins_includes']['render_includes']['target']);
        static::assertTrue($this->assignedValues['plugins_includes']['render_includes']['loggedin']);
        static::assertSame('templateName', $this->assignedValues['plugins_header']['render_header']['target']);
        static::assertTrue($this->assignedValues['plugins_header']['render_header']['loggedin']);
        static::assertSame('templateName', $this->assignedValues['plugins_footer']['render_footer']['target']);
        static::assertTrue($this->assignedValues['plugins_footer']['render_footer']['loggedin']);
    }

    protected function createValidContainerMockSet(): void
    {
        $pageBuilder = $this->createMock(PageBuilder::class);
        $pageBuilder
            ->method('assign')
            ->willReturnCallback(function (string $key, $value): void {
                $this->assignedValues[$key] = $value;
            });
        $pageBuilder
            ->method('render')
            ->willReturnCallback(function (string $template): string {
                return $template;
            });
        $this->container->pageBuilder = $pageBuilder;

        $bookmarkService = $this->createMock(BookmarkServiceInterface::class);
        $bookmarkService
            ->method('count')
            ->willReturnCallback(function (string $visibility): int {
                return $visibility === BookmarkFilter::$PRIVATE ? 5 : 10;
            });
        $this->container->bookmarkService = $bookmarkService;

        $pluginManager = $this->createMock(PluginManager::class);
        $pluginManager
            ->method('executeHooks')
            ->willReturnCallback(function (string $hook, array &$data, array $params): array {
                return $data[$hook] = $params;
            });
        $pluginManager->method('getErrors')->willReturn(['error']);
        $this->container->pluginManager = $pluginManager;

        $loginManager = $this->createMock(LoginManager::class);
        $loginManager->method('isLoggedIn')->willReturn(true);
        $this->container->loginManager = $loginManager;
    }
}
