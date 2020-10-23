<?php

declare(strict_types=1);

namespace Shaarli\Front\Controller\Admin\ShaarePublishControllerTest;

use Shaarli\Front\Controller\Admin\FrontAdminControllerMockHelper;
use Shaarli\Front\Controller\Admin\ShaarePublishController;
use Shaarli\Http\HttpAccess;
use Shaarli\Http\MetadataRetriever;
use Shaarli\TestCase;
use Slim\Http\Request;
use Slim\Http\Response;

class DisplayCreateBatchFormTest extends TestCase
{
    use FrontAdminControllerMockHelper;

    /** @var ShaarePublishController */
    protected $controller;

    public function setUp(): void
    {
        $this->createContainer();

        $this->container->httpAccess = $this->createMock(HttpAccess::class);
        $this->container->metadataRetriever = $this->createMock(MetadataRetriever::class);
        $this->controller = new ShaarePublishController($this->container);
    }

    /**
     * TODO
     */
    public function testDisplayCreateFormBatch(): void
    {
        $urls = [
            'https://domain1.tld/url1',
            'https://domain2.tld/url2',
            ' ',
            'https://domain3.tld/url3',
        ];

        $request = $this->createMock(Request::class);
        $request->method('getParam')->willReturnCallback(function (string $key) use ($urls): ?string {
            return $key === 'urls' ? implode(PHP_EOL, $urls) : null;
        });
        $response = new Response();

        $assignedVariables = [];
        $this->assignTemplateVars($assignedVariables);

        $result = $this->controller->displayCreateBatchForms($request, $response);

        static::assertSame(200, $result->getStatusCode());
        static::assertSame('editlink.batch', (string) $result->getBody());

        static::assertTrue($assignedVariables['batch_mode']);
        static::assertCount(3, $assignedVariables['links']);
        static::assertSame($urls[0], $assignedVariables['links'][0]['link']['url']);
        static::assertSame($urls[1], $assignedVariables['links'][1]['link']['url']);
        static::assertSame($urls[3], $assignedVariables['links'][2]['link']['url']);
    }
}
