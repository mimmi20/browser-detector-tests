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
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use UaResult\Result\Result;
use UaResult\Result\ResultFactory;

/**
 * Class UserAgentsTest
 *
 * @category   CompareTest
 *
 * @author     Thomas Mueller <mimmi20@live.de>
 * @group      useragenttest
 */
abstract class UserAgentsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \BrowserDetector\BrowserDetector
     */
    protected $object = null;

    /**
     * @var \Monolog\Logger
     */
    protected static $logger = null;

    /**
     * @var \Psr\Cache\CacheItemPoolInterface
     */
    protected static $cache = null;

    /**
     * @var string
     */
    protected $sourceDirectory = 'tests/issues/00000/';

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new Detector(static::getCache(), static::getLogger());
    }

    /**
     * @return array[]
     */
    public function userAgentDataProvider()
    {
        $start = microtime(true);

        echo 'starting provider ', static::class, ' ...';

        $data     = [];
        $iterator = new \DirectoryIterator($this->sourceDirectory);

        foreach ($iterator as $file) {
            /** @var $file \SplFileInfo */
            if (!$file->isFile() || $file->getExtension() !== 'json') {
                continue;
            }

            $tests = json_decode(file_get_contents($file->getPathname()));

            foreach ($tests as $key => $test) {
                if (isset($data[$key])) {
                    // Test data is duplicated for key
                    continue;
                }

                $data[$key] = [
                    'ua'     => $test->ua,
                    'result' => (new ResultFactory())->fromArray(static::getCache(), static::getLogger(), (array) $test->result),
                ];
            }
        }

        echo ' finished (', str_pad(number_format(microtime(true) - $start, 4), 8, ' ', STR_PAD_LEFT), ' sec., ', str_pad((string) count($data), 6, ' ', STR_PAD_LEFT), ' test', (count($data) !== 1 ? 's' : ''), ')', PHP_EOL;

        return $data;
    }

    /**
     * @dataProvider userAgentDataProvider
     *
     * @param string                  $userAgent
     * @param \UaResult\Result\Result $expectedResult
     *
     * @throws \Exception
     */
    public function testUserAgents($userAgent, Result $expectedResult)
    {
        $result = $this->object->getBrowser($userAgent);

        static::assertInstanceOf(
            \UaResult\Result\Result::class,
            $result,
            'Expected result is not an instance of "\UaResult\Result\Result" for useragent "' . $userAgent . '"'
        );

        $foundBrowser = $result->getBrowser();

        static::assertInstanceOf(
            \UaResult\Browser\BrowserInterface::class,
            $foundBrowser,
            'Expected browser is not an instance of "\UaResult\Browser\BrowserInterface" for useragent "' . $userAgent . '"'
        );

        $foundEngine = $result->getEngine();

        static::assertInstanceOf(
            \UaResult\Engine\EngineInterface::class,
            $foundEngine,
            'Expected engine is not an instance of "\UaResult\Engine\EngineInterface" for useragent "' . $userAgent . '"'
        );

        $foundPlatform = $result->getOs();

        static::assertInstanceOf(
            \UaResult\Os\OsInterface::class,
            $foundPlatform,
            'Expected platform is not an instance of "\UaResult\Os\OsInterface" for useragent "' . $userAgent . '"'
        );

        $foundDevice = $result->getDevice();

        static::assertInstanceOf(
            \UaResult\Device\DeviceInterface::class,
            $foundDevice,
            'Expected result is not an instance of "\UaResult\Device\DeviceInterface" for useragent "' . $userAgent . '"'
        );

        self::assertEquals($expectedResult, $result);
    }

    /**
     * @return \Psr\Cache\CacheItemPoolInterface
     */
    protected static function getCache()
    {
        if (null !== static::$cache) {
            return static::$cache;
        }

        static::$cache = new FilesystemAdapter('', 0, __DIR__ . '/../cache/');

        return static::$cache;
    }

    /**
     * @return \Monolog\Logger
     */
    protected static function getLogger()
    {
        if (null !== static::$logger) {
            return static::$logger;
        }

        static::$logger = new Logger('browser-detector-tests');
        static::$logger->pushHandler(new NullHandler());

        return static::$logger;
    }
}
