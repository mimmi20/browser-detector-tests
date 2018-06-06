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

use BrowserDetector\DetectorFactory;
use Psr\Log\NullLogger;
use Psr\SimpleCache\CacheInterface;
use Seld\JsonLint\JsonParser;
use Seld\JsonLint\ParsingException;
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
     * @return void
     */
    protected function setUp(): void
    {
        $logger = $this->getMockBuilder(NullLogger::class)
            ->disableOriginalConstructor()
            ->setMethods(['info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency'])
            ->getMock();
        $logger
            ->expects(self::never())
            ->method('info');
        $logger
            ->expects(self::never())
            ->method('notice');
        $logger
            ->expects(self::never())
            ->method('warning');
        $logger
            ->expects(self::never())
            ->method('error');
        $logger
            ->expects(self::never())
            ->method('critical');
        $logger
            ->expects(self::never())
            ->method('alert');
        $logger
            ->expects(self::never())
            ->method('emergency');

        $factory      = new DetectorFactory(static::getCache(), $logger);
        $this->object = $factory();
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

        $jsonParser = new JsonParser();
        $logger     = new NullLogger();

        foreach ($finder as $file) {
            /* @var \Symfony\Component\Finder\SplFileInfo $file */

            try {
                $tests = $jsonParser->parse($file->getContents(), JsonParser::DETECT_KEY_CONFLICTS | JsonParser::PARSE_TO_ASSOC);
            } catch (ParsingException $e) {
                throw new \Exception(sprintf('file "%s" contains invalid json', $file->getPathname()), 0, $e);
            }

            foreach ($tests as $key => $test) {
                if (isset($data[$key])) {
                    // Test data is duplicated for key
                    continue;
                }

                $data[$key] = [
                    'result' => (new ResultFactory())->fromArray($logger, $test),
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
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *
     * @return void
     */
    public function testUserAgents(ResultInterface $expectedResult): void
    {
        $headers = $expectedResult->getHeaders();
        $object  = $this->object;
        $result  = $object($headers);

        static::assertInstanceOf(
            ResultInterface::class,
            $result,
            sprintf(
                'found result is not an instance of "\UaResult\Result\ResultInterface" for headers %s',
                json_encode($headers, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            )
        );

        $foundBrowser = $result->getBrowser();

        static::assertInstanceOf(
            BrowserInterface::class,
            $foundBrowser,
            sprintf(
                'found browser is not an instance of "\UaResult\Browser\BrowserInterface" for headers %s',
                json_encode($headers, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            )
        );

        $foundEngine = $result->getEngine();

        static::assertInstanceOf(
            EngineInterface::class,
            $foundEngine,
            sprintf(
                'found engine is not an instance of "\UaResult\Engine\EngineInterface" for headers %s',
                json_encode($headers, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            )
        );

        $foundPlatform = $result->getOs();

        static::assertInstanceOf(
            OsInterface::class,
            $foundPlatform,
            sprintf(
                'found platform is not an instance of "\UaResult\Os\OsInterface" for headers %s',
                json_encode($headers, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            )
        );

        $foundDevice = $result->getDevice();

        static::assertInstanceOf(
            DeviceInterface::class,
            $foundDevice,
            sprintf(
                'found result is not an instance of "\UaResult\Device\DeviceInterface" for headers %s',
                json_encode($headers, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            )
        );

        self::assertEquals(
            $expectedResult,
            $result,
            sprintf(
                'detection result mismatch for headers %s',
                json_encode($headers, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            )
        );
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
}
