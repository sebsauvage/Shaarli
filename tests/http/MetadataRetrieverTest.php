<?php

declare(strict_types=1);

namespace Shaarli\Http;

use PHPUnit\Framework\TestCase;
use Shaarli\Config\ConfigManager;

class MetadataRetrieverTest extends TestCase
{
    /** @var MetadataRetriever */
    protected $retriever;

    /** @var ConfigManager */
    protected $conf;

    /** @var HttpAccess */
    protected $httpAccess;

    public function setUp(): void
    {
        $this->conf = $this->createMock(ConfigManager::class);
        $this->httpAccess = $this->createMock(HttpAccess::class);
        $this->retriever = new MetadataRetriever($this->conf, $this->httpAccess);

        $this->conf->method('get')->willReturnCallback(function (string $param, $default) {
            return $default === null ? $param : $default;
        });
    }

    /**
     * Test metadata retrieve() with values returned
     */
    public function testFullRetrieval(): void
    {
        $url = 'https://domain.tld/link';
        $remoteTitle = 'Remote Title ';
        $remoteDesc = 'Sometimes the meta description is relevant.';
        $remoteTags = 'abc def';

        $expectedResult = [
            'title' => $remoteTitle,
            'description' => $remoteDesc,
            'tags' => $remoteTags,
        ];

        $this->httpAccess
            ->expects(static::once())
            ->method('getCurlDownloadCallback')
            ->willReturnCallback(
                function (&$charset, &$title, &$description, &$tags) use (
                    $remoteTitle,
                    $remoteDesc,
                    $remoteTags
                ): callable {
                    return function () use (
                        &$charset,
                        &$title,
                        &$description,
                        &$tags,
                        $remoteTitle,
                        $remoteDesc,
                        $remoteTags
                    ): void {
                        $charset = 'ISO-8859-1';
                        $title = $remoteTitle;
                        $description = $remoteDesc;
                        $tags = $remoteTags;
                    };
                }
            )
        ;
        $this->httpAccess
            ->expects(static::once())
            ->method('getHttpResponse')
            ->with($url, 30, 4194304)
            ->willReturnCallback(function($url, $timeout, $maxBytes, $callback): void {
                $callback();
            })
        ;

        $result = $this->retriever->retrieve($url);

        static::assertSame($expectedResult, $result);
    }

    /**
     * Test metadata retrieve() without any value
     */
    public function testEmptyRetrieval(): void
    {
        $url = 'https://domain.tld/link';

        $expectedResult = [
            'title' => null,
            'description' => null,
            'tags' => null,
        ];

        $this->httpAccess
            ->expects(static::once())
            ->method('getCurlDownloadCallback')
            ->willReturnCallback(
                function (&$charset, &$title, &$description, &$tags): callable {
                    return function () use (&$charset, &$title, &$description, &$tags): void {};
                }
            )
        ;
        $this->httpAccess
            ->expects(static::once())
            ->method('getHttpResponse')
            ->with($url, 30, 4194304)
            ->willReturnCallback(function($url, $timeout, $maxBytes, $callback): void {
                $callback();
            })
        ;

        $result = $this->retriever->retrieve($url);

        static::assertSame($expectedResult, $result);
    }
}
