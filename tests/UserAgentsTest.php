<?php
/**
 * Copyright (c) 1998-2014 Browser Capabilities Project
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * Refer to the LICENSE file distributed with this package.
 *
 * @category   CompareTest
 *
 * @copyright 2012-2016 Thomas Mueller
 * @license   http://www.opensource.org/licenses/MIT MIT License
 */

namespace BrowserDetectorTest;

use BrowserDetector\BrowserDetector;
use Cache\Adapter\Filesystem\FilesystemCachePool;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
use Monolog\Handler\NullHandler;
use Monolog\Logger;
use UaDataMapper\InputMapper;

/**
 * Class UserAgentsTest
 *
 * @category   CompareTest
 *
 * @author     Thomas Mueller <mimmi20@live.de>
 * @group      useragenttest
 */
abstract class UserAgentsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BrowserDetector\BrowserDetector
     */
    protected $object = null;

    /**
     * @var \UaDataMapper\InputMapper
     */
    protected static $mapper = null;

    /**
     * @var string
     */
    protected $sourceDirectory = 'tests/issues/00000/';

    /**
     * This method is called before the first test of this test class is run.
     *
     * @since Method available since Release 3.4.0
     */
    public static function setUpBeforeClass()
    {
        static::$mapper = new InputMapper();
    }

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $logger = new Logger('browser-detector-tests');
        $logger->pushHandler(new NullHandler());

        $adapter      = new Local(__DIR__ . '/../cache/');
        $cache        = new FilesystemCachePool(new Filesystem($adapter));
        $this->object = new BrowserDetector($cache, $logger);
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
                    'ua'         => $test->ua,
                    'properties' => (array) $test->properties,
                ];
            }
        }

        echo ' finished (', str_pad(number_format(microtime(true) - $start, 4), 8, ' ', STR_PAD_LEFT), ' sec., ', str_pad(count($data), 6, ' ', STR_PAD_LEFT), ' test', (count($data) <> 1 ? 's' : ''), ')', PHP_EOL;

        return $data;
    }

    /**
     * @dataProvider userAgentDataProvider
     *
     * @param string $userAgent
     * @param array  $expectedProperties
     *
     * @throws \Exception
     */
    public function testUserAgents($userAgent, $expectedProperties)
    {
        $result = $this->object->getBrowser($userAgent);

        static::assertInstanceOf(
            \UaResult\Result\Result::class,
            $result,
            'Expected result is not an instance of "\UaResult\Result\Result" for useragent "' . $userAgent . '"'
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

        static::assertArrayHasKey(
            'Device_Name',
            $expectedProperties,
            'Expected key "Device_Name" is missing for useragent "' . $userAgent . '"'
        );

        $expectedDeviceName = $expectedProperties['Device_Name'];
        $foundDeviceName    = $foundDevice->getMarketingName();

        static::assertInternalType('string', $foundDeviceName);

        static::assertSame(
            $expectedDeviceName,
            $foundDeviceName,
            'Expected actual "Device_Name" to be "' . $expectedDeviceName . '" (was "' . $foundDeviceName . '")'
        );

        static::assertArrayHasKey(
            'Device_Maker',
            $expectedProperties,
            'Expected key "Device_Maker" is missing for useragent "' . $userAgent . '"'
        );

        $expectedDeviceMaker = $expectedProperties['Device_Maker'];
        $foundDeviceMaker    = $foundDevice->getManufacturer();

        static::assertInternalType('string', $foundDeviceMaker);

        static::assertSame(
            $expectedDeviceMaker,
            $foundDeviceMaker,
            'Expected actual "Device_Maker" to be "' . $expectedDeviceMaker . '" (was "' . $foundDeviceMaker . '")'
        );

        static::assertArrayHasKey(
            'Device_Type',
            $expectedProperties,
            'Expected key "Device_Type" is missing for useragent "' . $userAgent . '"'
        );

        $expectedDeviceType = $expectedProperties['Device_Type'];
        $foundDeviceType    = $foundDevice->getType();

        static::assertInstanceOf(
            \UaDeviceType\TypeInterface::class,
            $foundDeviceType,
            'Expected result is not an instance of "\UaDeviceType\TypeInterface" for useragent "' . $userAgent . '"'
        );
        static::assertInstanceOf(
            $expectedDeviceType,
            $foundDeviceType,
            'Expected result is not an instance of "' . $expectedDeviceType . '" for useragent "' . $userAgent . '"'
        );

        static::assertArrayHasKey(
            'Device_Pointing_Method',
            $expectedProperties,
            'Expected key "Device_Pointing_Method" is missing for useragent "' . $userAgent . '"'
        );

        static::assertArrayHasKey(
            'Device_Code_Name',
            $expectedProperties,
            'Expected key "Device_Code_Name" is missing for useragent "' . $userAgent . '"'
        );

        $expectedDeviceCodeName = $expectedProperties['Device_Code_Name'];
        $foundDeviceCodeName    = $foundDevice->getDeviceName();

        static::assertInternalType('string', $foundDeviceCodeName);

        static::assertSame(
            $expectedDeviceCodeName,
            $foundDeviceCodeName,
            'Expected actual "Device_Code_Name" to be "' . $expectedDeviceCodeName . '" (was "' . $foundDeviceCodeName . '")'
        );

        static::assertArrayHasKey(
            'Device_Brand_Name',
            $expectedProperties,
            'Expected key "Device_Brand_Name" is missing for useragent "' . $userAgent . '"'
        );

        /*
        $expectedDeviceBrand = $expectedProperties['Device_Brand_Name'];
        $foundDeviceBrand    = $foundDevice->getBrand();

        static::assertInternalType('string', $foundDeviceBrand);

        static::assertSame(
            $expectedDeviceBrand,
            $foundDeviceBrand,
            'Expected actual "Device_Brand_Name" to be "' . $expectedDeviceBrand . '" (was "' . $foundDeviceBrand . '")'
        );
        /**/

        static::assertArrayHasKey(
            'Device_Dual_Orientation',
            $expectedProperties,
            'Expected key "Device_Dual_Orientation" is missing for useragent "' . $userAgent . '"'
        );

        $expectedDeviceOrientation = $expectedProperties['Device_Dual_Orientation'];
        $foundDeviceOrientation    = $foundDevice->getDualOrientation();

        static::assertSame(
            $expectedDeviceOrientation,
            $foundDeviceOrientation,
            'Expected actual "Device_Dual_Orientation" to be "' . $expectedDeviceOrientation . '" (was "' . $foundDeviceOrientation . '")'
        );

        static::assertArrayHasKey(
            'Platform_Codename',
            $expectedProperties,
            'Expected key "Platform_Codename" is missing for useragent "' . $userAgent . '"'
        );

        $expectedPlatformCodename = $expectedProperties['Platform_Codename'];
        $foundPlatformCodename    = $foundPlatform->getName();

        static::assertInternalType('string', $foundPlatformCodename);

        static::assertSame(
            $expectedPlatformCodename,
            $foundPlatformCodename,
            'Expected actual "Platform_Codename" to be "' . $expectedPlatformCodename . '" (was "' . $foundPlatformCodename . '")'
        );

        static::assertArrayHasKey(
            'Platform_Marketingname',
            $expectedProperties,
            'Expected key "Platform_Marketingname" is missing for useragent "' . $userAgent . '"'
        );

        $expectedPlatformMarketingname = $expectedProperties['Platform_Marketingname'];
        $foundPlatformMarketingname    = $foundPlatform->getMarketingName();

        static::assertInternalType('string', $foundPlatformMarketingname);

        static::assertSame(
            $expectedPlatformMarketingname,
            $foundPlatformMarketingname,
            'Expected actual "Platform_Marketingname" to be "' . $expectedPlatformMarketingname . '" (was "' . $foundPlatformMarketingname . '")'
        );

        static::assertArrayHasKey(
            'Platform_Maker',
            $expectedProperties,
            'Expected key "Platform_Maker" is missing for useragent "' . $userAgent . '"'
        );

        $expectedPlatformMaker = $expectedProperties['Platform_Maker'];
        $foundPlatformMaker    = $foundPlatform->getManufacturer();

        static::assertInternalType('string', $foundPlatformMaker);

        static::assertSame(
            $expectedPlatformMaker,
            $foundPlatformMaker,
            'Expected actual "Platform_Codename" to be "' . $expectedPlatformMaker . '" (was "' . $foundPlatformMaker . '")'
        );

        static::assertArrayHasKey(
            'Platform_Bits',
            $expectedProperties,
            'Expected key "Platform_Bits" is missing for useragent "' . $userAgent . '"'
        );

        $expectedPlatformBits = $expectedProperties['Platform_Bits'];
        $foundPlatformBits    = $foundPlatform->getBits();

        static::assertInternalType('integer', $foundPlatformBits);

        static::assertSame(
            $expectedPlatformBits,
            $foundPlatformBits,
            'Expected actual "Platform_Bits" to be "' . $expectedPlatformBits . '" (was "' . $foundPlatformBits . '")'
        );

        static::assertArrayHasKey(
            'Platform_Version',
            $expectedProperties,
            'Expected key "Platform_Version" is missing for useragent "' . $userAgent . '"'
        );

        $expectedPlatformVersion = $expectedProperties['Platform_Version'];
        $foundPlatformVersion    = $foundPlatform->getVersion();

        static::assertInstanceOf(\BrowserDetector\Version\Version::class, $foundPlatformVersion);

        static::assertSame(
            $expectedPlatformVersion,
            $foundPlatformVersion->getVersion(),
            'Expected actual "Platform_Version" to be "' . $expectedPlatformVersion . '" (was "' . $foundPlatformVersion->getVersion() . '")'
        );

        static::assertArrayHasKey(
            'Browser_Name',
            $expectedProperties,
            'Expected key "Browser_Name" is missing for useragent "' . $userAgent . '"'
        );

        /*
        $expectedBrowserName = $expectedProperties['Browser_Name'];
        $foundBrowserName    = $result->getBrowser()->getName();

        static::assertSame(
            $expectedBrowserName,
            $foundBrowserName,
            'Expected actual "Browser" to be "' . $expectedBrowserName . '" (was "' . $foundBrowserName . '")'
        );
        /**/

        static::assertArrayHasKey(
            'Browser_Type',
            $expectedProperties,
            'Expected key "Browser_Type" is missing for useragent "' . $userAgent . '"'
        );

        /*
        $expectedBrowserType = static::$mapper->mapBrowserType($expectedProperties['Browser_Type'])->getName();
        $foundBrowserType    = $result->getBrowser()->getType()->getName();

        static::assertSame(
            $expectedBrowserType,
            $foundBrowserType,
            'Expected actual "Browser_Type" to be "' . $expectedBrowserType . '" (was "' . $foundBrowserType . '")'
        );
        /**/

        static::assertArrayHasKey(
            'Browser_Bits',
            $expectedProperties,
            'Expected key "Browser_Bits" is missing for useragent "' . $userAgent . '"'
        );
        // @todo: add check for browser bits

        static::assertArrayHasKey(
            'Browser_Maker',
            $expectedProperties,
            'Expected key "Browser_Maker" is missing for useragent "' . $userAgent . '"'
        );

        /*
        $expectedBrowserMaker = $expectedProperties['Browser_Maker'];
        $foundBrowserMaker    = $result->getBrowser()->getManufacturer();

        static::assertSame(
            $expectedBrowserMaker,
            $foundBrowserMaker,
            'Expected actual "Browser_Maker" to be "' . $expectedBrowserMaker . '" (was "' . $foundBrowserMaker . '")'
        );
        /**/

        static::assertArrayHasKey(
            'Browser_Modus',
            $expectedProperties,
            'Expected key "Browser_Modus" is missing for useragent "' . $userAgent . '"'
        );
        // @todo: add check for browser modus

        static::assertArrayHasKey(
            'Browser_Version',
            $expectedProperties,
            'Expected key "Browser_Version" is missing for useragent "' . $userAgent . '"'
        );
        // @todo: add check for browser version

        static::assertArrayHasKey(
            'RenderingEngine_Name',
            $expectedProperties,
            'Expected key "RenderingEngine_Name" is missing for useragent "' . $userAgent . '"'
        );

        static::assertArrayHasKey(
            'RenderingEngine_Version',
            $expectedProperties,
            'Expected key "RenderingEngine_Version" is missing for useragent "' . $userAgent . '"'
        );

        static::assertArrayHasKey(
            'RenderingEngine_Maker',
            $expectedProperties,
            'Expected key "RenderingEngine_Maker" is missing for useragent "' . $userAgent . '"'
        );
    }
}
