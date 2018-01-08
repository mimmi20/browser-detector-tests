<?php
/**
 * This file is part of the browser-detector-tests package.
 *
 * Copyright (c) 2015-2017, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);
namespace BrowserDetectorTest;

use BrowserDetector\Detector;
use Monolog\Handler\NullHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Simple\FilesystemCache;
use UaResult\Result\ResultFactory;
use UaResult\Result\ResultInterface;

trait UserAgentsTestTrait
{
    /**
     * @var \BrowserDetector\Detector
     */
    private $object;

    /**
     * @var \Monolog\Logger
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
     * @return void
     */
    protected function setUp(): void
    {
        $this->object = new Detector(static::getCache(), static::getLogger());
        $this->object->warmupCache();
    }

    /**
     * @return array[]
     */
    public function userAgentDataProvider(): array
    {
        $start = microtime(true);

        echo 'starting provider ', static::class, ' ...';

        $data     = [];
        foreach ($this->sourceDirectory as $directory) {
            $iterator = new \DirectoryIterator($directory);

            foreach ($iterator as $file) {
                /** @var $file \SplFileInfo */
                if (!$file->isFile() || 'json' !== $file->getExtension()) {
                    continue;
                }

                $tests = json_decode(file_get_contents($file->getPathname()), true);

                foreach ($tests as $key => $test) {
                    if (isset($data[$key])) {
                        // Test data is duplicated for key
                        continue;
                    }

                    $data[$key] = [
                        'ua'     => $test['ua'],
                        'result' => (new ResultFactory())->fromArray(static::getLogger(), $test['result']),
                    ];
                }
            }
        }

        echo ' finished (', str_pad(number_format(microtime(true) - $start, 4), 7, ' ', STR_PAD_LEFT), ' sec., ', str_pad((string) count($data), 4, ' ', STR_PAD_LEFT), ' test', (1 !== count($data) ? 's' : ''), ')', PHP_EOL;

        return $data;
    }

    /**
     * @dataProvider userAgentDataProvider
     *
     * @param string                           $userAgent
     * @param \UaResult\Result\ResultInterface $expectedResult
     *
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws \Seld\JsonLint\ParsingException
     *
     * @return void
     */
    public function testUserAgents(string $userAgent, ResultInterface $expectedResult): void
    {
        $result = $this->object->getBrowser($userAgent);

        static::assertInstanceOf(
            \UaResult\Result\ResultInterface::class,
            $result,
            'Expected result is not an instance of "\UaResult\Result\ResultInterface" for useragent "' . $userAgent . '"'
        );

        $foundBrowser = $result->getBrowser();

        static::assertInstanceOf(
            \UaResult\Browser\BrowserInterface::class,
            $foundBrowser,
            'Expected browser is not an instance of "\UaResult\Browser\BrowserInterface" for useragent "' . $userAgent . '"'
        );

        self::assertEquals($expectedResult->getBrowser(), $foundBrowser);

        $foundEngine = $result->getEngine();

        static::assertInstanceOf(
            \UaResult\Engine\EngineInterface::class,
            $foundEngine,
            'Expected engine is not an instance of "\UaResult\Engine\EngineInterface" for useragent "' . $userAgent . '"'
        );

        self::assertEquals($expectedResult->getEngine(), $foundEngine);

        $foundPlatform = $result->getOs();

        static::assertInstanceOf(
            \UaResult\Os\OsInterface::class,
            $foundPlatform,
            'Expected platform is not an instance of "\UaResult\Os\OsInterface" for useragent "' . $userAgent . '"'
        );

        self::assertEquals($expectedResult->getOs(), $foundPlatform);

        $foundDevice = $result->getDevice();

        static::assertInstanceOf(
            \UaResult\Device\DeviceInterface::class,
            $foundDevice,
            'Expected result is not an instance of "\UaResult\Device\DeviceInterface" for useragent "' . $userAgent . '"'
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

        static::$logger = new Logger('browser-detector-tests');
        static::$logger->pushHandler(new NullHandler());

        return static::$logger;
    }
}
