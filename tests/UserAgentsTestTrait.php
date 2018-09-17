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
use ExceptionalJSON\DecodeErrorException;
use ExceptionalJSON\EncodeErrorException;
use JsonClass\Json;
use Psr\Log\NullLogger;
use Symfony\Component\Cache\Simple\NullCache;
use Symfony\Component\Finder\Finder;
use UaNormalizer\NormalizerFactory;
use UaRequest\GenericRequestFactory;
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
        /** @var \PHPUnit\Framework\MockObject\MockObject $logger */
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

        $cache = new NullCache();

        /** @var \Psr\Log\NullLogger $logger */
        $factory      = new DetectorFactory($cache, $logger);
        $this->object = $factory();
    }

    /**
     * @throws \RuntimeException
     * @throws \Exception
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

        $logger = new NullLogger();

        foreach ($finder as $file) {
            /* @var \Symfony\Component\Finder\SplFileInfo $file */

            try {
                $tests = (new Json())->decode($file->getContents(), true);
            } catch (DecodeErrorException $e) {
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

        $normalizer = (new NormalizerFactory())->build();
        $request    = (new GenericRequestFactory())->createRequestFromArray($headers);

        $data = [
            'all-headers' => $headers,
            'device-ua'   => $normalizer->normalize($request->getDeviceUserAgent()),
            'browser-ua'  => $normalizer->normalize($request->getBrowserUserAgent()),
        ];

        try {
            $encodedHeaders = (new Json())->encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (EncodeErrorException $e) {
            $encodedHeaders = '<failed to encode headers>';
        }

        static::assertInstanceOf(
            ResultInterface::class,
            $result,
            sprintf(
                'found result is not an instance of "\UaResult\Result\ResultInterface" for headers %s',
                $encodedHeaders
            )
        );

        $foundBrowser = $result->getBrowser();

        static::assertInstanceOf(
            BrowserInterface::class,
            $foundBrowser,
            sprintf(
                'found browser is not an instance of "\UaResult\Browser\BrowserInterface" for headers %s',
                $encodedHeaders
            )
        );

        $foundEngine = $result->getEngine();

        static::assertInstanceOf(
            EngineInterface::class,
            $foundEngine,
            sprintf(
                'found engine is not an instance of "\UaResult\Engine\EngineInterface" for headers %s',
                $encodedHeaders
            )
        );

        $foundPlatform = $result->getOs();

        static::assertInstanceOf(
            OsInterface::class,
            $foundPlatform,
            sprintf(
                'found platform is not an instance of "\UaResult\Os\OsInterface" for headers %s',
                $encodedHeaders
            )
        );

        $foundDevice = $result->getDevice();

        static::assertInstanceOf(
            DeviceInterface::class,
            $foundDevice,
            sprintf(
                'found result is not an instance of "\UaResult\Device\DeviceInterface" for headers %s',
                $encodedHeaders
            )
        );

        self::assertEquals(
            $expectedResult,
            $result,
            sprintf(
                'detection result mismatch for headers %s',
                $encodedHeaders
            )
        );
    }
}
