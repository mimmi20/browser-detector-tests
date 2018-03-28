<?php
/**
 * This file is part of the browser-detector-tests package.
 *
 * Copyright (c) 2015-2018, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);
namespace BrowserDetectorTest;

use BrowserDetector\Detector;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Simple\FilesystemCache;
use Symfony\Component\Finder\Finder;
use UaResult\Browser\BrowserInterface;
use UaResult\Device\DeviceInterface;
use UaResult\Engine\EngineInterface;
use UaResult\Os\OsInterface;
use UaResult\Result\ResultFactory;
use UaResult\Result\ResultInterface;

trait UserAgentsTestTrait
{
    /**
     * @var \BrowserDetector\Detector
     */
    private $object;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private static $logger = null;

    /**
     * @var \Psr\SimpleCache\CacheInterface
     */
    private static $cache = null;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Seld\JsonLint\ParsingException
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->object = new Detector(static::getCache(), static::getLogger());
        $this->object->warmupCache();
    }

    /**
     * @throws \RuntimeException
     *
     * @return array[]
     */
    public function userAgentDataProvider(): array
    {
        $start = microtime(true);

        echo 'starting provider ', static::class, ' ...';

        $data = [];

        $finder = new Finder();
        $finder->files();
        $finder->name('*.json');
        $finder->ignoreDotFiles(true);
        $finder->ignoreVCS(true);
        $finder->sortByName();
        $finder->ignoreUnreadableDirs();

        foreach ($this->sourceDirectory as $directory) {
            $finder->in($directory);
        }

        foreach ($finder as $file) {
            /** @var \Symfony\Component\Finder\SplFileInfo $file */
            if (!$file->isFile()) {
                continue;
            }

            if ('json' !== $file->getExtension()) {
                continue;
            }

            $tests = json_decode($file->getContents(), true);

            foreach ($tests as $key => $test) {
                if (isset($data[$key])) {
                    // Test data is duplicated for key
                    continue;
                }

                $data[$key] = [
                    'result' => (new ResultFactory())->fromArray(static::getLogger(), $test),
                ];
            }
        }

        echo ' finished (', str_pad(number_format(microtime(true) - $start, 4), 7, ' ', STR_PAD_LEFT), ' sec., ', str_pad((string) count($data), 4, ' ', STR_PAD_LEFT), ' test', (1 !== count($data) ? 's' : ''), ')', PHP_EOL;

        return $data;
    }

    /**
     * @dataProvider userAgentDataProvider
     *
     * @param ResultInterface $expectedResult
     *
     * @throws \Psr\Cache\InvalidArgumentException
     *
     * @return void
     */
    public function testUserAgents(ResultInterface $expectedResult): void
    {
        $result     = $this->object->parseArray($expectedResult->getHeaders());
        $userAgents = json_encode($expectedResult->getHeaders());

        static::assertInstanceOf(
            ResultInterface::class,
            $result,
            'Expected result is not an instance of "\UaResult\Result\ResultInterface" for useragent "' . $userAgents . '"'
        );

        $foundBrowser = $result->getBrowser();

        static::assertInstanceOf(
            BrowserInterface::class,
            $foundBrowser,
            'Expected browser is not an instance of "\UaResult\Browser\BrowserInterface" for useragent "' . $userAgents . '"'
        );

        self::assertEquals($expectedResult->getBrowser(), $foundBrowser);

        $foundEngine = $result->getEngine();

        static::assertInstanceOf(
            EngineInterface::class,
            $foundEngine,
            'Expected engine is not an instance of "\UaResult\Engine\EngineInterface" for useragent "' . $userAgents . '"'
        );

        self::assertEquals($expectedResult->getEngine(), $foundEngine);

        $foundPlatform = $result->getOs();

        static::assertInstanceOf(
            OsInterface::class,
            $foundPlatform,
            'Expected platform is not an instance of "\UaResult\Os\OsInterface" for useragent "' . $userAgents . '"'
        );

        self::assertEquals($expectedResult->getOs(), $foundPlatform);

        $foundDevice = $result->getDevice();

        static::assertInstanceOf(
            DeviceInterface::class,
            $foundDevice,
            'Expected result is not an instance of "\UaResult\Device\DeviceInterface" for useragent "' . $userAgents . '"'
        );

        self::assertEquals($expectedResult->getDevice(), $foundDevice);

        //self::assertEquals($expectedResult, $result);
    }

    /**
     * @return \Psr\SimpleCache\CacheInterface
     */
    private static function getCache(): CacheInterface
    {
        if (null !== static::$cache) {
            return static::$cache;
        }

        static::$cache = new FilesystemCache('', 0, __DIR__ . '/../cache/');

        return static::$cache;
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    private static function getLogger(): LoggerInterface
    {
        if (null !== static::$logger) {
            return static::$logger;
        }

        static::$logger = new NullLogger();

        return static::$logger;
    }
}
